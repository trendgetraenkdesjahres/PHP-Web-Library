<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier;

/**
 * Classifies decoded API response payloads by applying scorer classes for 'content', 'meta' and 'error'.
 */
class PayloadClassifier
{
    /** @var array<class-string<ScorerInterface>> List of scorer classes */
    protected array $scorer_classes = [];

    /**
     * Constructor.
     *
     * @param array<class-string<ScorerInterface>> $scorer_classes Scorer classes to apply.
     */
    public function __construct(array $scorer_classes = [])
    {
        $this->scorer_classes = $scorer_classes;
    }

    /**
     * Classify a decoded API response payload.
     *
     * @param array $data Decoded API response data.
     * @return PayloadClassification Classification result object.
     */
    public function classify(array $data): PayloadClassification
    {
        $results = [];
        $this->traverse($data, $results);
        return PayloadClassification::from_results($results);
    }

    /**
     * Recursively traverse the data tree and score each element.
     *
     * @param array $data Current data node.
     * @param array<int,ClassificationResult> $results Accumulated classification results.
     * @param string $path Dot-notated path to current node.
     * @param int $depth Current recursion depth.
     */
    protected function traverse(array $data, array &$results, string $path = '', int $depth = 0): void
    {
        foreach ($data as $key => $value) {
            $current_path = $path === '' ? $key : "$path.$key";

            $scores = [
                'meta' => 0.0,
                'error' => 0.0,
                'content' => 0.0,
            ];

            foreach ($this->scorer_classes as $scorer_class) {
                if (!method_exists($scorer_class, 'score')) {
                    continue;
                }

                $partial = $scorer_class::score($key, $value, $depth);

                foreach ($partial as $type => $score) {
                    $scores[$type] += $score;
                }
            }

            $results[] = new ClassificationResult($current_path, $value, $scores);

            if ($this->should_descend($value, $depth)) {
                $this->traverse($value, $results, $current_path, $depth + 1);
            }
        }
    }

    /**
     * Determine if the traversal should descend into the current value.
     *
     * @param mixed $value Value to evaluate.
     * @param int $depth Current recursion depth.
     * @return bool True if should descend, false otherwise.
     */
    protected function should_descend(mixed $value, int $depth): bool
    {
        return is_array($value) && $depth < 3;
    }
}
