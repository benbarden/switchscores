<?php

namespace App\Domain\GamePublisher;

use Illuminate\Support\Facades\DB;

use App\Enums\GameStatus;
use App\Models\DataSource;
use App\Models\Game;

class DbQueries
{
    /**
     * @param $publisherId
     * @param bool $releasedOnly
     * @param null $limit
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function getGamesByPublisher($publisherId, $releasedOnly = false, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->join('games_companies', 'game_publishers.publisher_id', '=', 'games_companies.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*',
                'categories.name AS category_name',
                'game_publishers.publisher_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_publishers.publisher_id', $publisherId);

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

    public function byPublisherRanked($publisherId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->join('games_companies', 'game_publishers.publisher_id', '=', 'games_companies.id')
            ->select('games.*',
                'game_publishers.publisher_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_publishers.publisher_id', $publisherId)
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->whereNotNull('games.game_rank')
            ->orderBy('games.game_rank', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function byPublisherUnranked($publisherId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->join('games_companies', 'game_publishers.publisher_id', '=', 'games_companies.id')
            ->select('games.*',
                'game_publishers.publisher_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_publishers.publisher_id', $publisherId)
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->whereNull('games.game_rank')
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function byPublisherDelisted($publisherId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->join('games_companies', 'game_publishers.publisher_id', '=', 'games_companies.id')
            ->select('games.*',
                'game_publishers.publisher_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_publishers.publisher_id', $publisherId)
            ->where('games.game_status', GameStatus::DELISTED->value)
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();
    }

    public function getGamesWithNoPublisher()
    {
        $games = DB::table('games')
            ->leftJoin('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->leftJoin('games_companies', 'game_publishers.publisher_id', '=', 'games_companies.id')
            ->leftJoin('data_source_parsed AS dsp_nintendo_co_uk', function ($join) {
                $join->on('games.id', '=', 'dsp_nintendo_co_uk.game_id')
                    ->where('dsp_nintendo_co_uk.source_id', '=', DataSource::DSID_NINTENDO_CO_UK);
            })
            ->select('games.*',
                'dsp_nintendo_co_uk.publishers AS nintendo_co_uk_publishers',
                'games_companies.name')
            ->whereNull('games_companies.id')
            //->where('games.format_digital', '<>', Game::FORMAT_DELISTED)
            //->orWhereNull('games.format_digital')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countGamesWithNoPublisher()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_publishers gp on g.id = gp.game_id
            left join games_companies gc on gc.id = gp.publisher_id
            where gc.id is null
        ');

        return $games[0]->count;
    }

}