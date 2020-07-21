<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\GameGenre;


class GameGenreService
{
    public function createGameGenreList(
        $gameId, $genreIdList
    )
    {
        if (count($genreIdList) == 0) return false;

        foreach ($genreIdList as $genreId) {
            $this->create($gameId, $genreId);
        }
    }

    public function create($gameId, $genreId)
    {
        return GameGenre::create([
            'game_id' => $gameId,
            'genre_id' => $genreId
        ]);
    }

    public function deleteGameGenres(
        $gameId
    )
    {
        GameGenre::where('game_id', $gameId)->delete();
    }

    // ********************************************************** //

    public function getByGame($gameId)
    {
        return GameGenre::where('game_id', $gameId)->get();
    }

    /**
     * @return mixed
     */
    public function getGamesWithoutGenres()
    {
        $games = DB::table('games')
            ->leftJoin('game_genres', 'games.id', '=', 'game_genres.game_id')
            ->select('games.*',
                'game_genres.genre_id')
            ->where('games.eu_is_released', '1')
            ->whereNull('game_genres.genre_id')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }
}