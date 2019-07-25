<?php

namespace App\Construction\GameReleaseDate;


use App\Game;
use App\GameReleaseDate;
use Illuminate\Http\Request;

class Director
{
    /**
     * @var Builder
     */
    private $builder;

    public function setBuilder(Builder $builder): void
    {
        $this->builder = $builder;
    }

    public function setGameId(): void
    {
        $game = $this->builder->getGame();
        if ($game != null) {
            $this->builder->setGameId($this->builder->getGame()->id);
        }
    }

    public function getRegionList()
    {
        return [
            GameReleaseDate::REGION_EU,
            GameReleaseDate::REGION_US,
            GameReleaseDate::REGION_JP,
        ];
    }

    public function buildNewReleaseDate($region, $gameId, $params): void
    {
        $this->builder->setRegion($region);
        $this->builder->setGameId($gameId);
        $this->buildReleaseDate($params);
    }

    public function buildExistingReleaseDate($region, GameReleaseDate $gameReleaseDate, $params): void
    {
        $this->builder->setRegion($region);
        $this->builder->setGameReleaseDate($gameReleaseDate);
        $this->buildReleaseDate($params);
    }

    public function buildReleaseDate($params): void
    {
        $region = $this->builder->getGameReleaseDate()->region;

        $releaseDateField = 'release_date_'.$region;
        if (array_key_exists($releaseDateField, $params)) {
            $releaseDate = $params[$releaseDateField];
            $releaseYear = $this->builder->getReleaseYear($releaseDate);
            $this->builder->setReleaseDate($params[$releaseDateField]);
            $this->builder->setReleaseYear($releaseYear);
        }

        $isReleasedField = 'is_released_'.$region;
        if (array_key_exists($isReleasedField, $params)) {
            $isReleased = $params[$isReleasedField] == 'on' ? 1 : 0;
            $this->builder->setIsReleased($isReleased);
        } else {
            $this->builder->setIsReleased(0);
        }

        $isLockedField = 'is_locked_'.$region;
        if (array_key_exists($isLockedField, $params)) {
            $isLocked = $params[$isLockedField] == 'on' ? 1 : 0;
            $this->builder->setIsLocked($isLocked);
        } else {
            $this->builder->setIsLocked(0);
        }

        $upcomingDateField = 'upcoming_date_'.$region;
        if (array_key_exists($upcomingDateField, $params)) {
            $this->builder->setUpcomingDate($params[$upcomingDateField]);
        }
    }
}