<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier;

class ValueStructureScorer implements ScorerInterface
{
    public static function score(string $key, mixed $value, int $depth): array
    {
        $scores = [
            'meta' => 0.0,
            'error' => 0.0,
            'content' => 0.0,
        ];

        if (!is_array($value) || empty($value)) {
            return $scores;
        }

        // Flat array of scalars → likely meta (e.g. ['count' => 10, 'page' => 1])
        if (self::is_flat_scalar_map($value)) {
            $scores['meta'] += 0.4;
            return self::adjust_by_depth($scores, $depth);
        }

        // List of uniform objects → likely content collection
        if (self::is_uniform_object_list($value)) {
            $scores['content'] += 0.9;
            return self::adjust_by_depth($scores, $depth);
        }

        // Associative array with mixed types → likely a single item
        if (self::is_object_like($value)) {
            $scores['content'] += 0.6;
            return self::adjust_by_depth($scores, $depth);
        }

        return self::adjust_by_depth($scores, $depth);
    }

    protected static function is_flat_scalar_map(array $value): bool
    {
        foreach ($value as $v) {
            if (is_array($v) || is_object($v)) {
                return false;
            }
        }
        return self::is_associative($value);
    }

    protected static function is_uniform_object_list(array $value): bool
    {
        if (!self::is_list($value)) {
            return false;
        }

        $first = reset($value);
        if (!is_array($first)) {
            return false;
        }

        $first_keys = array_keys($first);

        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }
            if (array_keys($item) !== $first_keys) {
                return false;
            }
        }

        return true;
    }

    protected static function is_object_like(array $value): bool
    {
        return self::is_associative($value);
    }

    protected static function is_associative(array $array): bool
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    protected static function is_list(array $array): bool
    {
        $i = 0;
        foreach ($array as $k => $_) {
            if ($k !== $i++) {
                return false;
            }
        }
        return true;
    }

    protected static function adjust_by_depth(array $scores, int $depth): array
    {
        $penalty = 1 / ($depth + 1);
        foreach ($scores as $type => $score) {
            $scores[$type] = round($score * $penalty, 3);
        }
        return $scores;
    }
}
