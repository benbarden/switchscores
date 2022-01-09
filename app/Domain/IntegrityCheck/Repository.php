<?php


namespace App\Domain\IntegrityCheck;

use App\Models\Game;
use App\Models\IntegrityCheck;

class Repository
{
    public function getAll()
    {
        return IntegrityCheck::orderBy('entity_name', 'asc')->orderBy('check_name', 'asc')->get();
    }

    public function getByName($name)
    {
        return IntegrityCheck::where('check_name', $name)->first();
    }

    public function getGameMissingRank()
    {
        return Game::where('review_count', '>', 2)
            ->whereNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('id', 'asc')->get();
    }

    public function getGameNoReleaseYear()
    {
        return Game::whereNull('release_year')
            ->orWhere('release_year', '0')
            ->orderBy('id', 'asc')->get();
    }

    public function getGameWrongReleaseYear()
    {
        $noReleaseYearList = $this->getGameNoReleaseYear()->pluck('id');
        return Game::whereRaw("YEAR(eu_release_date) != release_year")
            ->whereNotIn('id', $noReleaseYearList)
            ->orderBy('id', 'asc')->get();
    }
}