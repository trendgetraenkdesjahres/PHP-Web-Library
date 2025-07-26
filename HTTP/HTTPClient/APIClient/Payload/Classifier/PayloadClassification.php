<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier;

/**
 * Classifies payload elements by inferred type: content, error, or meta.
 *
 * Results are scored and top type is selected per field path. Allows selective
 * flattening and grouping for structured extraction of API response elements.
 */
class PayloadClassification
{
    /** @var array Results from the classification engine */
    protected array $results = [];

    /** @var array<string,string|null> Map of field path to winning type */
    protected array $winning_types = [];

    /** @var array<string,mixed> Original path-to-result mapping */
    protected array $path_map = [];

    /** @var array<string,array> Memoized child paths grouped by winning type */
    protected array $child_paths_by_type = [];

    /** @var bool Verbose output flag for diagnostics */
    public static bool $verbose = false;

    /**
     * Construct from raw classification results.
     *
     * @param array $results Classification objects with path, value, scores
     * @return self
     */
    public static function from_results(array $results): self
    {
        $instance = new self();
        $instance->results = $results;

        foreach ($results as $result) {
            $path = $result->path;
            $type = self::resolve_winning_type($result->scores);

            $instance->winning_types[$path] = $type;
            $instance->path_map[$path] = $result;

            if (self::$verbose) {
                $scores_str = json_encode($result->scores);
                $chosen = $type ?? 'none (below threshold)';
                echo "[PayloadClassification] Path: '{$path}', Scores: {$scores_str}, Chosen: {$chosen}\n";
            }
        }

        return $instance;
    }

    /**
     * Return array containing structured API response sections.
     *
     * @return array
     */
    public function to_array(): array
    {
        return [
            'meta' => $this->get_meta(),
            'error' => $this->get_error(),
            'content' => $this->get_content(),
        ];
    }

    /**
     * Get top-level payload fields classified as content.
     *
     * Fields with no classification also included as fallback.
     *
     * Example return:
     * [
     *   "user" => [
     *     "id" => 1,
     *     "name" => "Alice"
     *   ],
     *   "token" => "abc123"
     * ]
     *
     * @return array
     */
    public function get_content(): array
    {
        $content = $this->extract_top_level_by_type('content');
        $fallback = $this->get_fallback_content();
        return array_replace_recursive($fallback, $content);
    }

    /**
     * Get top-level payload fields classified as error.
     *
     * Example return:
     * [
     *   "code" => 403,
     *   "message" => "Access denied"
     * ]
     *
     * @return array
     */
    public function get_error(): array
    {
        return $this->extract_top_level_by_type('error');
    }

    /**
     * Get fields classified as meta, excluding any inside content or error.
     *
     * Example return:
     * [
     *   "request_id" => "abc123",
     *   "debug" => ["mode" => true]
     * ]
     *
     * @return array
     */
    public function get_meta(): array
    {
        $excluded = array_merge(
            $this->get_all_child_paths('content'),
            $this->get_all_child_paths('error')
        );

        $excluded_roots = array_map(fn($p) => explode('.', $p)[0], $excluded);
        $meta = [];

        foreach ($this->results as $result) {
            $path = $result->path;
            $winner = $this->winning_types[$path] ?? null;

            if ($winner !== 'meta') {
                continue;
            }

            foreach ($excluded as $child_path) {
                if (str_starts_with($child_path, $path . '.')) {
                    continue 2;
                }
            }

            if (substr_count($path, '.') === 0 && in_array($path, $excluded_roots, true)) {
                continue;
            }

            self::insert_by_path($meta, $path, $result->value);
        }

        return $meta;
    }

    /**
     * Selects the winning classification type for a field, based on score.
     *
     * Returns `null` if highest score is under 0.5 threshold.
     *
     * @param array<string,float> $scores
     * @return string|null
     */
    protected static function resolve_winning_type(array $scores): ?string
    {
        arsort($scores);
        $top = array_key_first($scores);
        return ($scores[$top] ?? 0.0) >= 0.5 ? $top : null;
    }

    /**
     * Recursively insert a value into an array at a dotted path.
     *
     * Example:
     * insert_by_path($arr, 'user.name.first', 'Alice')
     * → $arr becomes [ 'user' => [ 'name' => [ 'first' => 'Alice' ]]]
     *
     * @param array $target
     * @param string $path
     * @param mixed $value
     * @return void
     */
    protected static function insert_by_path(array &$target, string $path, mixed $value): void
    {
        $parts = explode('.', $path);
        $ref = &$target;
        foreach ($parts as $part) {
            if (!isset($ref[$part]) || !is_array($ref[$part])) {
                $ref[$part] = [];
            }
            $ref = &$ref[$part];
        }
        $ref = $value;
    }

    /**
     * Extract top-level keys for the given type, skipping nested children.
     *
     * Also collapses nesting when there is only a single key branch.
     *
     * Example input paths:
     *   'data.user.id', 'data.user.name', 'errors.0.code'
     * If 'data' is selected, all child fields under 'data.*' are ignored.
     *
     * @param string $type
     * @return array
     */
    protected function extract_top_level_by_type(string $type): array
    {
        $top = [];
        $selected_paths = [];

        foreach ($this->results as $result) {
            $path = $result->path;

            if (($this->winning_types[$path] ?? null) !== $type) {
                continue;
            }

            foreach ($selected_paths as $selected) {
                if (str_starts_with($path, $selected . '.')) {
                    continue 2;
                }
            }

            $selected_paths[] = $path;
            self::insert_by_path($top, $path, $result->value);
        }

        // Collapse structure if there's only one non-numeric top-level key
        for ($i = 0; $i < 3; $i++) {
            if (count($top) !== 1) return $top;
            $only_key = array_key_first($top);
            if (is_numeric($only_key)) return $top;

            $only_element = $top[$only_key];
            if (!is_array($only_element)) return $top;
            if (array_keys($only_element) === range(0, count($only_element) - 1)) return $only_element;

            $top = $only_element;
        }

        return $top;
    }

    /**
     * Get all paths assigned to a given type, including nested descendants.
     *
     * Example:
     * If `a.b.c` is of type 'content', this returns:
     *   [ 'a.b.c', 'a.b.c.d', 'a.b.c.e.f', ... ]
     *
     * @param string $type
     * @return array<string>
     */
    protected function get_all_child_paths(string $type): array
    {
        if (isset($this->child_paths_by_type[$type])) {
            return $this->child_paths_by_type[$type];
        }

        $paths = [];

        foreach ($this->path_map as $path => $result) {
            if (($this->winning_types[$path] ?? null) !== $type) {
                continue;
            }

            $paths[] = $path;
            $prefix = $path . '.';

            foreach ($this->path_map as $child_path => $_) {
                if (str_starts_with($child_path, $prefix)) {
                    $paths[] = $child_path;
                }
            }
        }

        $this->child_paths_by_type[$type] = array_unique($paths);
        return $this->child_paths_by_type[$type];
    }

    /**
     * Get any result field not classified as content, error, or meta.
     *
     * Used to preserve fields missed by classification engine.
     *
     * @return array
     */
    protected function get_fallback_content(): array
    {
        $assigned = array_merge(
            $this->get_all_child_paths('content'),
            $this->get_all_child_paths('error'),
            $this->get_all_child_paths('meta')
        );

        $fallback = [];

        foreach ($this->results as $result) {
            $path = $result->path;
            if (!in_array($path, $assigned, true)) {
                self::insert_by_path($fallback, $path, $result->value);
            }
        }

        return $fallback;
    }
}
