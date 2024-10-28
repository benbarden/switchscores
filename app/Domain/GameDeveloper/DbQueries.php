<?php

namespace App\Domain\GameDeveloper;

use Illuminate\Support\Facades\DB;

use App\Models\Game;

class DbQueries
{
    /**
     * @param $developerId
     * @param bool $releasedOnly
     * @param null $limit
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function getGamesByDeveloper($developerId, $releasedOnly = false, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->join('games_companies', 'game_developers.developer_id', '=', 'games_companies.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*',
                'categories.name AS category_name',
                'game_developers.developer_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_developers.developer_id', $developerId);

        if ($releasedOnly) {
            $games = $games->where('games.eu_is_released', '1');
        }

        $games = $games->orderBy('games.eu_release_date', 'desc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        $games = $games->get();
        return $games;
    }

    public function byDeveloperRanked($developerId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->join('games_companies', 'game_developers.developer_id', '=', 'games_companies.id')
            ->select('games.*',
                'game_developers.developer_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_developers.developer_id', $developerId)
            ->where('games.format_digital', '<>', Game::FORMAT_DELISTED)
            ->whereNotNull('games.game_rank')
            ->orderBy('games.game_rank', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function byDeveloperUnranked($developerId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->join('games_companies', 'game_developers.developer_id', '=', 'games_companies.id')
            ->select('games.*',
                'game_developers.developer_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_developers.developer_id', $developerId)
            ->where('games.format_digital', '<>', Game::FORMAT_DELISTED)
            ->whereNull('games.game_rank')
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function byDeveloperDelisted($developerId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->join('games_companies', 'game_developers.developer_id', '=', 'games_companies.id')
            ->select('games.*',
                'game_developers.developer_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_developers.developer_id', $developerId)
            ->where('games.format_digital', '=', Game::FORMAT_DELISTED)
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function getGamesWithNoDeveloper()
    {
        $games = DB::table('games')
            ->leftJoin('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->leftJoin('games_companies', 'game_developers.developer_id', '=', 'games_companies.id')
            ->select('games.*',
                'games_companies.name')
            ->whereNull('games_companies.id')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countGamesWithNoDeveloper()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_developers gd on g.id = gd.game_id
            left join games_companies gc on gc.id = gd.developer_id
            where gc.id is null
        ');

        return $games[0]->count;
    }

}