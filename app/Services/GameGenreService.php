<?php


namespace App\Services;

use App\GameGenre;


class GameGenreService
{
    public function createGameGenreList(
        $gameId, $genreIdList
    )
    {
        if (count($genreIdList) == 0) return false;

        foreach ($genreIdList as $genreId) {
            GameGenre::create([
                'game_id' => $gameId,
                'genre_id' => $genreId
            ]);
        }
    }

    public function deleteGameGenres(
        $gameId
    )
    {
        GameGenre::where('game_id', $gameId)->delete();
    }

    public function getByGame($gameId)
    {
        return GameGenre::where('game_id', $gameId)->get();
    }
}