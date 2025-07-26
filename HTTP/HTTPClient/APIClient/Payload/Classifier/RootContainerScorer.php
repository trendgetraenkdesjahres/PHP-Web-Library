<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier;

/**
 * Scores root-level containers based on typical meta keys and content structure.
 */
class RootContainerScorer implements ScorerInterface
{
    /**
     * Score a key-value pair for likelihood to be meta, error, or content.
     *
     * @param string $key Key name.
     * @param mixed $value Associated value.
     * @param int $depth Depth in the payload tree.
     * @return array<string,float> Scores by classification type.
     */
    public static function score(string $key, mixed $value, int $depth): array
    {
        if (!is_array($value)) {
            return ['meta' => 0.0, 'error' => 0.0, 'content' => 0.0];
        }

        $keys = array_keys($value);
        $meta_like_keys = ['limit', 'offset', 'total', 'next', 'previous', 'href', 'count'];

        $meta_keys_found = count(array_intersect($keys, $meta_like_keys));

        $content_like_keys = 0;
        foreach ($value as $child_key => $child_val) {
            if (is_array($child_val) && self::is_array_of_objects($child_val)) {
                $content_like_keys++;
            }
        }

        // If container mixes meta keys and exactly one array-of-objects key, score as meta container with negative content score
        if ($meta_keys_found > 0 && $content_like_keys === 1) {
            return [
                'meta' => 0.7,
                'content' => -0.7,
                'error' => 0.0,
            ];
        }

        return ['meta' => 0.0, 'error' => 0.0, 'content' => 0.0];
    }

    /**
     * Check if the array is an array of objects (arrays).
     *
     * @param array $arr Array to check.
     * @return bool True if all elements are arrays and array is not empty.
     */
    protected static function is_array_of_objects(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }

        foreach ($arr as $item) {
            if (!is_array($item)) {
                return false;
            }
        }

        return true;
    }
}
