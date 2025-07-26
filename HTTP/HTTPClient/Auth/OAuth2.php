<?php

namespace PHP_Library\HTTP\HTTPClient\Auth;

use PHP_Library\HTTP\HTTPClient\APIClient\APIClient;
use PHP_Library\HTTP\HTTPClient\APIClient\Payload\Item;
use PHP_Library\HTTP\HTTPClient\Auth\AuthCredentials\AuthCredentials;
use PHP_Library\HTTP\HTTPClient\Auth\Error\AuthError;
use PHP_Library\HTTP\HTTPClient\HTTP1Client;
use PHP_Library\HTTP\HTTPResponse\HTTPResponse;

/**
 * OAuth2 authentication client.
 *
 * Manages OAuth2 client credentials and authorization code flow.
 * Handles token retrieval, refresh, and Authorization header construction.
 */
class OAuth2 extends AbstractAuth
{
    /** @var string OAuth2 client secret */
    public readonly string $client_secret;

    public readonly string $user_code_request_endpoint;

    /** @var string OAuth2 token endpoint URL */
    public readonly string $token_endpoint;

    /** @var string|null Redirect URI for authorization code grant */
    public readonly string $redirect_uri;

    public readonly string $scopes;

    protected AuthCredentials $credentials_interface;

    /**
     * OAuth2 constructor.
     *
     * @param string $client_id OAuth2 client identifier
     * @param string $client_secret OAuth2 client secret
     * @param string $token_endpoint OAuth2 token endpoint URI
     * @param string|null $redirect_uri Redirect URI for auth code grant
     */
    public function __construct(string $client_id, string $client_secret, string $token_endpoint, ?string $redirect_uri, string $user_code_request_endpoint = 'authorize', string ...$scopes)
    {
        $this->credentials_interface = new AuthCredentials(
            $this,
            $token_endpoint,
            'access_token',
            'token_expires',
            'refresh_token'
        );
        $this->user_code_request_endpoint = $user_code_request_endpoint;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->token_endpoint = $token_endpoint;
        $this->redirect_uri = $redirect_uri;
        $this->scopes = implode(" ", $scopes);
    }

    /**
     * Get query parameters for authentication (none for OAuth2 bearer).
     *
     * @return array<string,mixed>
     */
    public function get_query_params(): array
    {
        return [];
    }

    /**
     * Get HTTP Authorization header with bearer token.
     *
     * Refreshes token if expired.
     *
     * @return array<string,string> Authorization header
     * @throws AuthError
     */
    public function get_header_fields(): array
    {
        if ($this->token_expired()) {
            $refresh_token = $this->credentials_interface->get('refresh_token');
            if ($refresh_token && $this->refresh_access_token()) {
                return ['Authorization' => "Bearer {$refresh_token}"];
            }
            $this->get_first_access_token();
        }
        $access_token = $this->credentials_interface->get('access_token');

        return ['Authorization' => "Bearer {$access_token}"];
    }

    /**
     * Obtain user code via authorization endpoint.
     *
     * @return string User authorization code
     * @throws AuthError
     */
    protected function get_user_code(): string
    {
        $user_code_request_url = static::get_user_code_request_url($this->user_code_request_endpoint);
        $user_code_getter = new HTTP1Client($user_code_request_url, 'GET');
        $api_response = $user_code_getter->send()->response ?? false;

        if (!$api_response) {
            throw new AuthError("No response for user code request from '{$user_code_request_url}'");
        }

        return $this->extract_user_code_from_response($user_code_request_url, $api_response);
    }

    /**
     * Obtain first access token using authorization code grant.
     *
     * @return bool True on successful token retrieval
     * @throws AuthError
     */
    protected function get_first_access_token(): bool
    {
        $user_code = $this->get_user_code();

        $token_item = $this->get_token_item([
            'grant_type' => 'authorization_code',
            'code' => $user_code,
            'redirect_uri' => $this->redirect_uri
        ]);

        return $this->credentials_interface->update_credentials_with_token_item($token_item);
    }

    /**
     * Refresh access token using the refresh token.
     *
     * @return bool True on successful token refresh
     * @throws AuthError
     */
    protected function refresh_access_token(): bool
    {
        $refresh_token = $this->credentials_interface->get('refresh_token');
        if (!$refresh_token) {
            throw new AuthError("No refresh token available.");
        }

        $token_item = $this->get_token_item([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        ]);

        return $this->credentials_interface->update_credentials_with_token_item($token_item);
    }

    /**
     * Build full URL for user code request.
     *
     * @param string $endpoint Endpoint path or full URL
     * @return string Full URL with query string
     * @throws AuthError
     */
    private function get_user_code_request_url(string $endpoint): string
    {
        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $endpoint = '/' . ltrim($endpoint, '/');
            $url_parts = parse_url($this->token_endpoint);

            if (!$url_parts) {
                throw new AuthError("Cannot build user code request URL from '{$this->token_endpoint}'.");
            }

            $url = HTTP1Client::build_url(
                $url_parts['scheme'],
                $url_parts['host'],
                $url_parts['port'] ?? null,
                $endpoint
            );
        } else {
            $url = $endpoint;
        }

        $query_parameters = [
            'client_id' => $this->client_id,
            'response_type' => 'code',
            'redirect_uri' => $this->redirect_uri
        ];
        if($this->scopes) {
            $query_parameters['scope'] = $this->scopes;
        }

        return $url . '?' . http_build_query($query_parameters);
    }

    /**
     * Extract user authorization code from response or prompt manually.
     *
     * @param string $url_for_user_code URL used to request user code
     * @param HTTPResponse|null $response HTTP response object
     * @return string Authorization code
     * @throws AuthError
     */
    private function extract_user_code_from_response(string $url_for_user_code, ?HTTPResponse $response): string
    {
        $location_header = $response?->get_header_field('Location');

        if (
            !$location_header ||
            parse_url($location_header, PHP_URL_HOST) !== parse_url($this->redirect_uri, PHP_URL_HOST)
        ) {
            $redirect_location = static::manual_user_authorization($url_for_user_code);
        } else {
            $redirect_location = $location_header;
        }

        $query = parse_url($redirect_location, PHP_URL_QUERY);
        if (!$query) {
            throw new AuthError("Cannot parse query from redirect location: '{$redirect_location}'.");
        }

        parse_str($query, $query_parts);
        if (!isset($query_parts['code'])) {
            throw new AuthError("Missing 'code' in redirect location: '{$redirect_location}'.");
        }

        return $query_parts['code'];
    }

    /**
     * Prompt user to manually authorize and provide redirect URI.
     *
     * @param string $url_for_user_code Authorization URL
     * @return string User-pasted redirect URL
     */
    private static function manual_user_authorization(string $url_for_user_code): string
    {
        echo "Open and accept: {$url_for_user_code}" . PHP_EOL;
        return readline("Paste the redirect url: ");
    }

    /**
     * Check if the current access token is expired.
     *
     * @return bool True if token expired
     */
    private function token_expired(): bool
    {
        var_dump(
            date('D, d M Y H:i:s', (int) $this->credentials_interface->get('token_expires')),
            date('D, d M Y H:i:s', date())
        );
        return time() >= (int) $this->credentials_interface->get('token_expires');
    }

    /**
     * Send request to token endpoint and retrieve token payload.
     *
     * @param array<string,mixed> $data Token request parameters
     * @return Item Token payload item
     * @throws AuthError
     */
    private function get_token_item(array $data): Item
    {
        $token_generator = new APIClient(
            url: $this->token_endpoint,
            default_method: 'POST',
            data: $data,
            auth: new Basic($this->client_id, $this->client_secret)
        );

        $token_generator->set_header_field('Content-Type: application/x-www-form-urlencoded');

        try {
            $token_generator->send();
        } catch (\Throwable $e) {
            $token_type = isset($data['grant_type']) ? $data['grant_type'] . '-' : '';
            throw new AuthError("Could not retrieve OAuth2 {$token_type}token.", $token_generator, $token_generator->response);
        }

        return $token_generator->get_item();
    }
}
