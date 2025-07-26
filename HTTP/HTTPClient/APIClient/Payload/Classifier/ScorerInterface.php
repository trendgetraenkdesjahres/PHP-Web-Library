<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier;

/**
 * Interface for scoring payload elements by classification type.
 */
interface ScorerInterface
{
    /**
     * Score a key-value pair at a given depth in the payload tree.
     *
     * @param string $key Key name.
     * @param mixed $value Associated value.
     * @param int $depth Current depth in payload traversal.
     * @return array<string,float> Scores keyed by classification type: ['meta' => float, 'error' => float, 'content' => float]
     */
    public static function score(string $key, mixed $value, int $depth): array;
}
