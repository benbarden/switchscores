<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\GamePublisher;
use App\Partner;


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
     * @param $region
     * @param $publisherId
     * @param null $limit
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function getGamesByPublisher($region, $publisherId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->join('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->join('partners', 'game_publishers.publisher_id', '=', 'partners.id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year',
                'game_publishers.publisher_id',
                'partners.name')
            ->where('game_publishers.publisher_id', $publisherId)
            ->where('game_release_dates.region', $region)
            //->where('game_release_dates.is_released', '1')
            ->orderBy('game_release_dates.release_date', 'desc');
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
            ->select('games.*', 'partners.name')
            ->whereNull('partners.id')
            ->whereNull('games.publisher')
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
            where p.id is null and g.publisher is null;
        ');

        return $games[0]->count;
    }

    public function getNewPublishersToSet()
    {
        $games = DB::table('games')
            ->leftJoin('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->leftJoin('partners', 'game_publishers.publisher_id', '=', 'partners.id')
            ->select('games.*', 'partners.name')
            ->whereNull('partners.id')
            ->whereNotNull('games.publisher')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countNewPublishersToSet()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_publishers gp on g.id = gp.game_id
            left join partners p on p.id = gp.publisher_id
            where p.id is null and g.publisher is not null;
        ');

        return $games[0]->count;
    }

    public function getOldPublishersToClear()
    {
        $games = DB::table('games')
            ->leftJoin('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->leftJoin('partners', 'game_publishers.publisher_id', '=', 'partners.id')
            ->select('games.*', 'partners.name')
            ->whereNotNull('partners.id')
            ->whereNotNull('games.publisher')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countOldPublishersToClear()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_publishers gp on g.id = gp.game_id
            left join partners p on p.id = gp.publisher_id
            where p.id is not null and g.publisher is not null;
        ');

        return $games[0]->count;
    }

    public function getGamePublisherLinks()
    {
        $games = DB::table('games')
            ->leftJoin('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->leftJoin('partners', 'game_publishers.publisher_id', '=', 'partners.id')
            ->select('games.*', 'partners.name')
            ->whereNotNull('partners.id')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countGamePublisherLinks()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_publishers gp on g.id = gp.game_id
            left join partners p on p.id = gp.publisher_id
            where p.id is not null;
        ');

        return $games[0]->count;
    }
}