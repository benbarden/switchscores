<?php

namespace App\Domain\Game\Repository;

use App\Models\Game;

use Illuminate\Support\Collection;

class CategoryVerificationRepository
{
    public function getOldestUnverifiedGames(int $limit = 15): Collection
    {
        return Game::query()
            ->where('category_verification', Game::VERIF_UNVERIFIED)
            ->where('is_low_quality', 0)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
    }
}