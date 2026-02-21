<?php

namespace App\Services\Game;

class TitleMatch
{
    private $matchRule;

    private $matchIndex;

    public function setMatchRule($matchRule)
    {
        $this->matchRule = $matchRule;
    }

    public function getMatchRule()
    {
        return $this->matchRule;
    }

    public function prepareMatchRule()
    {
        if (!str_starts_with($this->matchRule, "/^")) {
            $this->matchRule = "/^".$this->matchRule;
        }
        if (!str_ends_with($this->matchRule, "$/")) {
            $this->matchRule .= "$/";
        }
    }

    public function setMatchIndex($matchIndex)
    {
        $this->matchIndex = $matchIndex;
    }

    public function generate($title)
    {
        if ($this->matchRule == '') return $title;

        preg_match_all($this->matchRule, $title, $matches);

        if (count($matches) == 0) return null;

        if (array_key_exists($this->matchIndex, $matches)) {
            if (array_key_exists(0, $matches[$this->matchIndex])) {
                return $matches[$this->matchIndex][0];
            } else {
                return null;
            }
        }

        return null;
    }
}