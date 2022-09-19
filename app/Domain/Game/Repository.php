<?php


namespace App\Domain\Game;

use App\Models\Game;

class Repository
{
    public function find($id)
    {
        return Game::find($id);
    }

    public function randomGame()
    {
        return Game::where('eu_is_released', 1)->whereNotNull('game_rank')->where('is_low_quality', 0)->inRandomOrder()->first();
    }

    /**
     * @param $title
     * @param null $excludeGameId
     * @return bool
     */
    public function titleExists($title, $excludeGameId = null): bool
    {
        $game = Game::where('title', $title);
        if ($excludeGameId) {
            $game = $game->where('id', '<>', $excludeGameId);
        }
        $game = $game->first();
        return $game != null;
    }

    /**
     * @param $title
     * @return \App\Models\Game|null
     */
    public function getByTitle($title)
    {
        return Game::where('title', $title)->first();
    }

    /**
     * @param $idList
     * @param string[] $orderBy
     * @return \Illuminate\Support\Collection
     */
    public function getByIdList($idList, $orderBy = "")
    {
        if ($orderBy) {
            list($orderField, $orderDir) = $orderBy;
        } else {
            list($orderField, $orderDir) = ['id', 'desc'];
        }

        $idList = str_replace('&quot;', '', $idList);
        $idList = explode(",", $idList);

        $games = Game::whereIn('games.id', $idList)->orderBy($orderField, $orderDir);

        return $games->get();
    }
}