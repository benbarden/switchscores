<?php


namespace App\Services\Game;


class QualityScore
{
    const RULE_HAS_CATEGORY = 'has_category';
    const RULE_HAS_DEVELOPERS = 'has_developers';
    const RULE_HAS_PUBLISHERS = 'has_publishers';
    const RULE_HAS_PLAYERS = 'has_players';
    const RULE_HAS_PRICE = 'has_price';
    const RULE_NO_CONFLICT_NINTENDO_EU_RELEASE_DATE = 'no_conflict_nintendo_eu_release_date';
    const RULE_NO_CONFLICT_NINTENDO_PRICE = 'no_conflict_nintendo_price';
    const RULE_NO_CONFLICT_NINTENDO_PLAYERS = 'no_conflict_nintendo_players';
    const RULE_NO_CONFLICT_NINTENDO_PUBLISHERS = 'no_conflict_nintendo_publishers';
    const RULE_NO_CONFLICT_NINTENDO_GENRE = 'no_conflict_nintendo_genre';
    const RULE_NO_CONFLICT_WIKIPEDIA_EU_RELEASE_DATE = 'no_conflict_wikipedia_eu_release_date';
    const RULE_NO_CONFLICT_WIKIPEDIA_US_RELEASE_DATE = 'no_conflict_wikipedia_us_release_date';
    const RULE_NO_CONFLICT_WIKIPEDIA_JP_RELEASE_DATE = 'no_conflict_wikipedia_jp_release_date';
    const RULE_NO_CONFLICT_WIKIPEDIA_DEVELOPERS = 'no_conflict_wikipedia_developers';
    const RULE_NO_CONFLICT_WIKIPEDIA_PUBLISHERS = 'no_conflict_wikipedia_publishers';
    const RULE_NO_CONFLICT_WIKIPEDIA_GENRE = 'no_conflict_wikipedia_genre';

    /**
     * @var string[]
     */
    private $rules;

    private $gameRules;

    public function __construct()
    {
        $this->rules = [
            self::RULE_HAS_CATEGORY,
            self::RULE_HAS_DEVELOPERS,
            self::RULE_HAS_PUBLISHERS,
            self::RULE_HAS_PLAYERS,
            self::RULE_HAS_PRICE,
            self::RULE_NO_CONFLICT_NINTENDO_EU_RELEASE_DATE,
            self::RULE_NO_CONFLICT_NINTENDO_PRICE,
            self::RULE_NO_CONFLICT_NINTENDO_PLAYERS,
            self::RULE_NO_CONFLICT_NINTENDO_PUBLISHERS,
            self::RULE_NO_CONFLICT_NINTENDO_GENRE,
            self::RULE_NO_CONFLICT_WIKIPEDIA_EU_RELEASE_DATE,
            self::RULE_NO_CONFLICT_WIKIPEDIA_US_RELEASE_DATE,
            self::RULE_NO_CONFLICT_WIKIPEDIA_JP_RELEASE_DATE,
            self::RULE_NO_CONFLICT_WIKIPEDIA_DEVELOPERS,
            self::RULE_NO_CONFLICT_WIKIPEDIA_PUBLISHERS,
            self::RULE_NO_CONFLICT_WIKIPEDIA_GENRE,
        ];
    }

    public function getRuleCount()
    {
        return count($this->rules);
    }

    public function getGameRules()
    {
        return $this->gameRules;
    }

    public function setHasCategory($pass)
    {
        $this->addGameRule(self::RULE_HAS_CATEGORY, $pass);
    }

    public function setHasDevelopers($pass)
    {
        $this->addGameRule(self::RULE_HAS_DEVELOPERS, $pass);
    }

    public function setHasPublishers($pass)
    {
        $this->addGameRule(self::RULE_HAS_PUBLISHERS, $pass);
    }

    public function setHasPlayers($pass)
    {
        $this->addGameRule(self::RULE_HAS_PLAYERS, $pass);
    }

    public function setHasPrice($pass)
    {
        $this->addGameRule(self::RULE_HAS_PRICE, $pass);
    }

    public function setNoConflictNintendoEUReleaseDate($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_NINTENDO_EU_RELEASE_DATE, $pass);
    }

    public function setNoConflictNintendoPrice($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_NINTENDO_PRICE, $pass);
    }

    public function setNoConflictNintendoPlayers($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_NINTENDO_PLAYERS, $pass);
    }

    public function setNoConflictNintendoPublishers($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_NINTENDO_PUBLISHERS, $pass);
    }

    public function setNoConflictNintendoGenre($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_NINTENDO_GENRE, $pass);
    }

    public function setNoConflictWikipediaEUReleaseDate($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_WIKIPEDIA_EU_RELEASE_DATE, $pass);
    }

    public function setNoConflictWikipediaUSReleaseDate($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_WIKIPEDIA_US_RELEASE_DATE, $pass);
    }

    public function setNoConflictWikipediaJPReleaseDate($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_WIKIPEDIA_JP_RELEASE_DATE, $pass);
    }

    public function setNoConflictWikipediaDevelopers($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_WIKIPEDIA_DEVELOPERS, $pass);
    }

    public function setNoConflictWikipediaPublishers($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_WIKIPEDIA_PUBLISHERS, $pass);
    }

    public function setNoConflictWikipediaGenre($pass)
    {
        $this->addGameRule(self::RULE_NO_CONFLICT_WIKIPEDIA_GENRE, $pass);
    }

    public function addGameRule($rule, $pass)
    {
        if (!in_array($rule, $this->rules)) {
            throw new \Exception('Unknown rule: '.$rule);
        }

        if ($pass == 1) {
            $this->gameRules[$rule] = 1;
        } else {
            $this->gameRules[$rule] = 0;
        }
    }

    public function assignRemainingAsFailed()
    {
        foreach ($this->rules as $rule) {
            if (!in_array($rule, $this->gameRules)) {
                $this->addGameRule($rule, 0);
            }
        }
    }

    public function countPassingRules()
    {
        $totalPassing = 0;
        foreach ($this->gameRules as $gameRule) {
            if ($gameRule == 1) {
                $totalPassing++;
            }
        }
        return $totalPassing;
    }

    public function countFailingRules()
    {
        $totalFailing = 0;
        foreach ($this->gameRules as $gameRule) {
            if ($gameRule == 0) {
                $totalFailing++;
            }
        }
        return $totalFailing;
    }

    public function calculateQualityScore()
    {
        $totalRules = $this->getRuleCount();
        $totalPassing = $this->countPassingRules();
        $qualityScore = round(($totalPassing / $totalRules) * 100, 2);
        $qualityScore = number_format($qualityScore, 2);
        return $qualityScore;
    }
}