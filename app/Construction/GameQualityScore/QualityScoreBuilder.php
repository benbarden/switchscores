<?php

namespace App\Construction\GameQualityScore;

use App\GameQualityScore;

class QualityScoreBuilder
{
    /**
     * @var GameQualityScore
     */
    private $gameQualityScore;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->gameQualityScore = new GameQualityScore();
    }

    public function getGameQualityScore(): GameQualityScore
    {
        return $this->gameQualityScore;
    }

    public function setGameQualityScore(GameQualityScore $gameQualityScore): void
    {
        $this->gameQualityScore = $gameQualityScore;
    }

    public function setGameId($value): QualityScoreBuilder
    {
        $this->gameQualityScore->game_id = $value;
        return $this;
    }

    public function setQualityScore($value): QualityScoreBuilder
    {
        $this->gameQualityScore->quality_score = $value;
        return $this;
    }

    public function setHasCategory($value): QualityScoreBuilder
    {
        $this->gameQualityScore->has_category = $value;
        return $this;
    }

    public function setHasDevelopers($value): QualityScoreBuilder
    {
        $this->gameQualityScore->has_developers = $value;
        return $this;
    }

    public function setHasPublishers($value): QualityScoreBuilder
    {
        $this->gameQualityScore->has_publishers = $value;
        return $this;
    }

    public function setHasPlayers($value): QualityScoreBuilder
    {
        $this->gameQualityScore->has_players = $value;
        return $this;
    }

    public function setHasPrice($value): QualityScoreBuilder
    {
        $this->gameQualityScore->has_price = $value;
        return $this;
    }

    public function setNoConflictNintendoEUReleaseDate($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_nintendo_eu_release_date = $value;
        return $this;
    }

    public function setNoConflictNintendoPrice($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_nintendo_price = $value;
        return $this;
    }

    public function setNoConflictNintendoPlayers($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_nintendo_players = $value;
        return $this;
    }

    public function setNoConflictNintendoPublishers($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_nintendo_publishers = $value;
        return $this;
    }

    public function setNoConflictNintendoGenre($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_nintendo_genre = $value;
        return $this;
    }

    public function setNoConflictWikipediaEUReleaseDate($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_wikipedia_eu_release_date = $value;
        return $this;
    }

    public function setNoConflictWikipediaUSReleaseDate($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_wikipedia_us_release_date = $value;
        return $this;
    }

    public function setNoConflictWikipediaJPReleaseDate($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_wikipedia_jp_release_date = $value;
        return $this;
    }

    public function setNoConflictWikipediaDevelopers($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_wikipedia_developers = $value;
        return $this;
    }

    public function setNoConflictWikipediaPublishers($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_wikipedia_publishers = $value;
        return $this;
    }

    public function setNoConflictWikipediaGenre($value): QualityScoreBuilder
    {
        $this->gameQualityScore->no_conflict_wikipedia_genre = $value;
        return $this;
    }
}