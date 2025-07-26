<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier;

/**
 * Scores keys based on name heuristics to classify payload elements.
 */
class KeyNameScorer implements ScorerInterface
{
    /** @var string[] Keywords indicating metadata */
    public const META_KEYWORDS = [
        'meta',
        'pagination',
        'paging',
        'nextPageToken',
        'page',
        'per_page',
        'count',
        'total',
        'limit',
        'offset',
        'next',
        'previous',
        'ok',
        'href',
        'kind',
        'etag',
        'regionCode',
        'info',
        'pageInfo'
    ];

    /** @var string[] Keywords indicating errors */
    public const ERROR_KEYWORDS = [
        'error',
        'errors',
        'status',
        'code',
        'type',
        'error_description',
    ];

    /** @var string[] Keywords indicating content */
    public const CONTENT_KEYWORDS = [
        'data',
        'items',
        'results',
        'content',
        'response',
        'entry',
        'resource',
        'records',
    ];

    /**
     * Scores a key name based on its likelihood to represent meta, error, or content.
     *
     * @param string $key Key name to score.
     * @param mixed $value Associated value (unused).
     * @param int $depth Current depth in payload tree.
     * @return array<string,float> Scores by type (meta, error, content).
     */
    public static function score(string $key, mixed $value, int $depth): array
    {
        $key_lc = strtolower($key);
        $scores = [
            'meta' => 0.0,
            'error' => 0.0,
            'content' => 0.0,
        ];

        if (in_array($key_lc, self::META_KEYWORDS, true)) {
            $scores['meta'] += 0.8;
        }

        if (in_array($key_lc, self::ERROR_KEYWORDS, true)) {
            $scores['error'] += 0.8;
        }

        if (in_array($key_lc, self::CONTENT_KEYWORDS, true)) {
            $scores['content'] += 0.8;
        }

        if (str_contains(strtolower($key_lc), 'meta')) {
            $scores['meta'] += 0.4;
        }
        if (str_contains(strtolower($key_lc), 'info')) {
            $scores['meta'] += 0.4;
        }
                if (str_contains(strtolower($key_lc), 'page')) {
            $scores['meta'] += 0.4;
        }

                        if (str_contains(strtolower($key_lc), 'token')) {
            $scores['meta'] += 0.4;
        }

        if (str_contains(strtolower($key_lc), 'error') || str_contains(strtolower($key_lc), 'exception')) {
            $scores['error'] += 0.4;
        }

        if (str_contains(strtolower($key_lc), 'data') || str_contains(strtolower($key_lc), 'result')) {
            $scores['content'] += 0.4;
        }

        // Apply penalty for deeper nesting to reduce score impact.
        $penalty = 1 / ($depth + 1);
        foreach ($scores as $type => $score) {
            $scores[$type] = round($score * $penalty, 3);
        }

        return $scores;
    }
}
