<?php

namespace App\Domain\GameTitleMatch;

class MatchRule
{
    private $matchRulePattern;

    private $matchRuleIndex;

    private $parsedTitle;

    public function __construct($matchRulePattern, $matchRuleIndex)
    {
        $this->matchRulePattern = $matchRulePattern;
        $this->matchRuleIndex = $matchRuleIndex;

        $this->prepareRule();
    }

    public function prepareRule()
    {
        if (!str_starts_with($this->matchRulePattern, "/^")) {
            $this->matchRulePattern = "/^".$this->matchRulePattern;
        }
        if (!str_ends_with($this->matchRulePattern, "$/")) {
            $this->matchRulePattern .= "$/";
        }
    }

    public function getPattern()
    {
        return $this->matchRulePattern;
    }

    public function getParsedTitle()
    {
        return $this->parsedTitle;
    }

    public function generateMatch($title)
    {
        if ($this->matchRulePattern == '') return $title;

        preg_match_all($this->matchRulePattern, $title, $matches);

        if (count($matches) == 0) return null;

        if (array_key_exists($this->matchRuleIndex, $matches)) {
            if (array_key_exists(0, $matches[$this->matchRuleIndex])) {
                $parsedTitle = $matches[$this->matchRuleIndex][0];
                // save the title before we put it in an array
                $this->parsedTitle = $parsedTitle;
                $titleMatches = $this->handleSpecialCharacters($parsedTitle);
                return $titleMatches;
            } else {
                return null;
            }
        }

        return null;
    }

    public function handleSpecialCharacters($parsedTitle)
    {
        // Check for curly quotes
        $titleMatches = [];
        $titleMatches[] = $parsedTitle;
        if (str_contains($parsedTitle, "’")) {
            $titleMatches[] = str_replace("’", "'", $parsedTitle);
        }

        return $titleMatches;
    }
}