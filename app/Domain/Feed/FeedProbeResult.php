<?php

namespace App\Domain\Feed;

/**
 * What a probe found in a feed.
 *
 * Every detection is paired with the evidence for it. The screen shows both, because a
 * bare answer ("Object mode") teaches nothing and cannot be sanity-checked, whereas the
 * reason ("the score is wrapped in CDATA, which array mode discards") can be read against
 * the feed by eye.
 */
class FeedProbeResult
{
    const SEVERITY_ERROR = 'error';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_NOTE = 'note';

    private $feedUrl;

    private $loadError = null;

    private $detections = [];

    private $warnings = [];

    private $sampleTitles = [];

    private $itemCount = 0;

    public function __construct($feedUrl = null)
    {
        $this->feedUrl = $feedUrl;
    }

    public function getFeedUrl()
    {
        return $this->feedUrl;
    }

    /**
     * A feed that could not be retrieved or parsed at all. Distinct from a feed that loaded
     * but looks wrong: the first tells you nothing, the second tells you plenty.
     */
    public function setLoadError($message)
    {
        $this->loadError = $message;
        return $this;
    }

    public function getLoadError()
    {
        return $this->loadError;
    }

    public function hasLoadError()
    {
        return $this->loadError !== null;
    }

    /**
     * @param string $field Name matching the form field it prefills, where there is one.
     * @param mixed $value
     * @param string $evidence Why the probe believes this, in a sentence.
     */
    public function setDetection($field, $value, $evidence = null)
    {
        $this->detections[$field] = ['value' => $value, 'evidence' => $evidence];
        return $this;
    }

    public function getDetection($field)
    {
        return array_key_exists($field, $this->detections) ? $this->detections[$field]['value'] : null;
    }

    public function getEvidence($field)
    {
        return array_key_exists($field, $this->detections) ? $this->detections[$field]['evidence'] : null;
    }

    public function hasDetection($field)
    {
        return array_key_exists($field, $this->detections) && $this->detections[$field]['value'] !== null;
    }

    public function getDetections()
    {
        return $this->detections;
    }

    public function addWarning($message, $severity = self::SEVERITY_WARNING)
    {
        $this->warnings[] = ['message' => $message, 'severity' => $severity];
        return $this;
    }

    public function getWarnings()
    {
        return $this->warnings;
    }

    public function getWarningsBySeverity($severity)
    {
        return array_values(array_filter($this->warnings, function ($warning) use ($severity) {
            return $warning['severity'] == $severity;
        }));
    }

    public function hasErrors()
    {
        return count($this->getWarningsBySeverity(self::SEVERITY_ERROR)) > 0;
    }

    public function setSampleTitles(array $titles)
    {
        $this->sampleTitles = $titles;
        return $this;
    }

    public function getSampleTitles()
    {
        return $this->sampleTitles;
    }

    public function setItemCount($count)
    {
        $this->itemCount = $count;
        return $this;
    }

    public function getItemCount()
    {
        return $this->itemCount;
    }
}
