<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\Payload\Classifier;

/**
 * Represents the classification result of a payload element.
 */
class ClassificationResult
{
    /** @var string Dot-notated path to the payload element */
    public string $path;

    /** @var mixed The value at the classified path */
    public mixed $value;

    /** @var array<string,float> Scores by classification type */
    public array $scores;

    /** @var string Label decided based on scores */
    public string $label;

    /**
     * Constructor.
     *
     * @param string $path Dot-notated path to the element.
     * @param mixed $value The payload element value.
     * @param array<string,float> $scores Classification scores by type.
     */
    public function __construct(string $path, mixed $value, array $scores)
    {
        $this->path = $path;
        $this->value = $value;
        $this->scores = $scores;
        $this->label = $this->decide_label($scores);
    }

    /**
     * Decide the label based on highest confidence score.
     *
     * @param array<string,float> $scores Scores by classification type.
     * @return string Label with confidence ≥ 0.6 or 'unknown' if none.
     */
    protected function decide_label(array $scores): string
    {
        arsort($scores);
        $top = array_key_first($scores);
        $confidence = $scores[$top];

        return $confidence >= 0.6 ? $top : 'unknown';
    }
}
