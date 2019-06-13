<?php

namespace App\Construction\GameReleaseDate;

use App\Game;
use App\GameReleaseDate;

class Builder
{
    /**
     * @var Game
     */
    private $game;

    /**
     * @var GameReleaseDate
     */
    private $gameReleaseDate;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->game = new Game;
        $this->gameReleaseDate = new GameReleaseDate;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function setGameId($gameId): void
    {
        $this->gameReleaseDate->game_id = $gameId;
    }

    public function getGameReleaseDate(): GameReleaseDate
    {
        return $this->gameReleaseDate;
    }

    public function setGameReleaseDate(GameReleaseDate $gameReleaseDate): void
    {
        $this->gameReleaseDate = $gameReleaseDate;
    }

    public function getReleaseYear($releaseDate): string
    {
        if ($releaseDate) {
            $releaseDateObject = new \DateTime($releaseDate);
            $releaseYear = $releaseDateObject->format('Y');
        } else {
            $releaseYear = '';
        }

        return $releaseYear;
    }

    public function setRegion($region): void
    {
        $this->gameReleaseDate->region = $region;
    }

    public function setReleaseDate($releaseDate): void
    {
        $this->gameReleaseDate->release_date = $releaseDate;
    }

    public function setIsReleased($isReleased): void
    {
        $this->gameReleaseDate->is_released = $isReleased;
    }

    public function setIsLocked($isLocked): void
    {
        $this->gameReleaseDate->is_locked = $isLocked;
    }

    public function setUpcomingDate($upcomingDate): void
    {
        $this->gameReleaseDate->upcoming_date = $upcomingDate;
    }

    public function setReleaseYear($releaseYear): void
    {
        $this->gameReleaseDate->release_year = $releaseYear;
    }
}