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
        if (substr($this->matchRule, 0, 2) != "/^") {
            $this->matchRule = "/^".$this->matchRule;
        }
        if (substr($this->matchRule, strlen($this->matchRule) - 2, 2) != "$/") {
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

        if (array_key_exists($this->matchIndex, $matches)) return $matches[$this->matchIndex][0];

        return null;
    }
}