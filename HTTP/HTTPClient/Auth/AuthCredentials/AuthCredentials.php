<?php

namespace PHP_Library\HTTP\HTTPClient\Auth\AuthCredentials;

use PHP_Library\Database\Database;
use PHP_Library\Database\Table\Column\Column;
use PHP_Library\Database\Table\Column\PrimaryKey;
use PHP_Library\Database\Table\DataTable;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Item;
use PHP_Library\HTTP\HTTPClient\Auth\AbstractAuth;

/**
 * Adapter to persist and retrieve credential-like properties for an AbstractAuth implementation.
 *
 * Creates a dedicated table on demand based on the auth class name.
 * Keeps auth classes storage-agnostic by centralizing DB interaction here.
 */
class AuthCredentials
{
    /** @var string Fully-qualified auth class name */
    public readonly string $auth_class_name;

    /** @var string Normalized table name storing credentials */
    public readonly string $table_name;

    /** @var string Unique service identifier (primary key) */
    protected readonly string $service_identifier;

    /** @var DataTable Interface to credential table */
    protected DataTable $data_interface;

    /**
     * Map of credential fields to stored string values.
     * Empty string means unset.
     *
     * @var array<string,string>
     */
    private array $credentials = [];

    /**
     * Construct credential adapter for given auth object.
     *
     * @param AbstractAuth $auth_object Auth instance needing credentials
     * @param string $service_identifier PK value for remote service
     * @param string ...$credential_names List of credential property names to persist
     */
    public function __construct(AbstractAuth $auth_object, string $service_identifier, string ...$credential_names)
    {
        $this->credentials = array_fill_keys($credential_names, '');
        $this->service_identifier = $service_identifier;

        $ref_class = new \ReflectionClass($auth_object::class);
        $this->auth_class_name = $ref_class->getName();

        $snake = preg_replace('/(?<!^)([A-Z])/', '_$1', $ref_class->getShortName());
        $this->table_name = preg_replace('/[^a-z]+/', '_', strtolower($snake));

        $this->init_db_interface(...$credential_names);
    }

    /**
     * Load stored credential values from database into $this->credentials.
     *
     * @return bool True if row found and loaded, false if none or error
     */
    public function update_auth_properties_from_db(): bool
    {
        try {
            $fields = array_keys($this->credentials);
            $statement = $this->data_interface
                ->select(...$fields)
                ->where_equals('service_identifier', $this->service_identifier);
            $values = $statement->get();

            foreach ($values as $field => $value) {
                $this->credentials[$field] = $value;
            }
            return (bool)$values;
        } catch (\Throwable) {
            // Swallow DB errors; caller handles failure
            return false;
        }
    }

    /**
     * Update credential values from OAuth2 token payload and persist.
     *
     * @param Item $token_item Token payload object from token endpoint
     * @return bool True on successful DB update, false otherwise
     */
    public function update_credentials_with_token_item(Item $token_item): bool
    {
        $updated = false;

        foreach (array_keys($this->credentials) as $field) {
            if ($field === 'token_expires') {
                // Store expiry as current time + expires_in seconds (RFC 6749)
                if(! property_exists($token_item, 'expires_in')) {
                    $token_item->expires_in = 60*60;
                }
                $this->credentials['token_expires'] = (string)($token_item->expires_in + time());
                $updated = true;
            } elseif (property_exists($token_item, $field)) {
                $this->credentials[$field] = (string)$token_item->$field;
                $updated = true;
            }
        }

        return $updated ? $this->update_db_with_credentials() : false;
    }

    /**
     * Retrieve a single credential value, loading from DB if necessary.
     *
     * @param string $credential_name Credential column name
     * @return string|false Credential value or false if unset
     */
    public function get(string $credential_name): string|false
    {
        if (!static::credentials_are_initialized($this->credentials)) {
            $this->update_auth_properties_from_db();
        }

        return $this->credentials[$credential_name] ?? false;
    }

    /**
     * Insert or update the credentials row in the DB.
     *
     * @return bool True on success, false on failure
     */
    protected function update_db_with_credentials(): bool
    {
        try {
            $row_exists = $this->data_interface
                ->select()
                ->where_equals('service_identifier', $this->service_identifier)
                ->execute();

            if (!$row_exists) {
                $new_data = ['service_identifier' => $this->service_identifier] + $this->credentials;
                $statement = $this->data_interface
                    ->insert(...array_keys($new_data))
                    ->values(...$new_data);
                $statement->execute();
            } else {
                $statement = $this->data_interface
                    ->update()
                    ->where_equals('service_identifier', $this->service_identifier);
                foreach ($this->credentials as $field => $value) {
                    $statement->set($field, $value);
                }
                $statement->execute();
            }
        } catch (\Throwable) {
            return false;
        }

        return true;
    }

    /**
     * Lazy-initialize the data interface and create table if missing.
     *
     * @param string ...$credential_names Columns to create in table
     */
    protected function init_db_interface(string ...$credential_names): void
    {
        if (!class_exists(Database::class)) {
            return; // No DB support in environment
        }

        if (!Database::table_exists($this->table_name)) {
            $schema = [new PrimaryKey('service_identifier', 'string')];
            foreach ($credential_names as $name) {
                $schema[] = new Column($name);
            }
            Database::create_table($this->table_name, ...$schema);
        }

        $this->data_interface = Database::get_table($this->table_name);
    }

    /**
     * Check if credentials array has any non-empty values.
     *
     * @param array<string,string> $credentials Credential map to check
     * @return bool True if any credential is set (non-empty string)
     */
    private static function credentials_are_initialized(array $credentials): bool
    {
        foreach ($credentials as $value) {
            if (is_string($value) && $value !== '') {
                return true;
            }
        }
        return false;
    }
}
