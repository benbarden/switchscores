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

    // ********************************************************** //

    public function getByGame($gameId)
    {
        return GameGenre::where('game_id', $gameId)->get();
    }

    /**
     * @param $region
     * @param $genreId
     * @return mixed
     */
    public function getGamesByGenre($region, $genreId)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->join('game_genres', 'games.id', '=', 'game_genres.game_id')
            ->join('genres', 'game_genres.genre_id', '=', 'genres.id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year',
                'game_genres.genre_id',
                'genres.genre')
            ->where('game_genres.genre_id', $genreId)
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', '1')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }

    /**
     * @param $region
     * @return mixed
     */
    public function getGamesWithoutGenres($region)
    {
        $games = DB::table('games')
            ->leftJoin('game_genres', 'games.id', '=', 'game_genres.game_id')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.id', 'games.title', 'games.link_title',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year',
                'game_genres.genre_id')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', '1')
            ->whereNull('game_genres.genre_id')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }
}