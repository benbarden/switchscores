<?php


namespace App\Services;

use App\Models\DataSource;
use App\Models\Game;
use App\Models\GamePublisher;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;


class GamePublisherService
{
    public function createGamePublisher($gameId, $publisherId)
    {
        GamePublisher::create([
            'game_id' => $gameId,
            'publisher_id' => $publisherId
        ]);
    }

    public function delete($gamePublisherId)
    {
        GamePublisher::where('id', $gamePublisherId)->delete();
    }

    public function deleteByGameId($gameId)
    {
        GamePublisher::where('game_id', $gameId)->delete();
    }

    public function find($id)
    {
        return GamePublisher::find($id);
    }

    // ********************************************************** //

    public function getByGame($gameId)
    {
        $gamePublishers = GamePublisher::where('game_id', '=', $gameId)->get();

        $gamePublishers = $gamePublishers->sortBy(function($gamePub) {
            return $gamePub->publisher->name;
        });

        return $gamePublishers;
    }

    public function getByPublisherId($publisherId)
    {
        return GamePublisher::where('publisher_id', $publisherId)->get();
    }

    public function gameHasPublisher($gameId, $publisherId)
    {
        $gameTag = GamePublisher::where('game_id', $gameId)
            ->where('publisher_id', $publisherId)
            ->first();
        if ($gameTag) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Helper method to get all of the publishers that haven't been applied to the current game yet.
     * @param $gameId
     * @return array
     */
    public function getPublishersNotOnGame($gameId)
    {
        $games = DB::select('
            select * from partners
            where type_id = ?
            and id not in (select publisher_id from game_publishers where game_id = ?)
            ORDER BY name
        ', [Partner::TYPE_GAMES_COMPANY, $gameId]);

        return $games;
    }

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
            ->join('partners', 'game_publishers.publisher_id', '=', 'partners.id')
            ->select('games.*',
                'game_publishers.publisher_id',
                'partners.name',
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

    // *** ACTION LISTS *** //

    public function getGamesWithNoPublisher()
    {
        $games = DB::table('games')
            ->leftJoin('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->leftJoin('partners', 'game_publishers.publisher_id', '=', 'partners.id')
            ->leftJoin('data_source_parsed AS dsp_nintendo_co_uk', function ($join) {
                $join->on('games.id', '=', 'dsp_nintendo_co_uk.game_id')
                    ->where('dsp_nintendo_co_uk.source_id', '=', DataSource::DSID_NINTENDO_CO_UK);
            })
            ->leftJoin('data_source_parsed AS dsp_wikipedia', function ($join) {
                $join->on('games.id', '=', 'dsp_wikipedia.game_id')
                    ->where('dsp_wikipedia.source_id', '=', DataSource::DSID_WIKIPEDIA);
            })
            ->select('games.*',
                'dsp_nintendo_co_uk.publishers AS nintendo_co_uk_publishers',
                'dsp_wikipedia.publishers AS wikipedia_publishers',
                'partners.name')
            ->whereNull('partners.id')
            ->where('games.format_digital', '<>', Game::FORMAT_DELISTED)
            ->orWhereNull('games.format_digital')
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
            left join partners p on p.id = gp.publisher_id
            where p.id is null
            and (format_digital <> ? OR format_digital IS NULL)
        ', [Game::FORMAT_DELISTED]);

        return $games[0]->count;
    }
}