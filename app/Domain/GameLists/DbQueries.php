<?php


namespace App\Domain\GameLists;


use App\Models\Game;
use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function getByTagWithDates($tagId)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'tags.tag_name')
            ->where('game_tags.tag_id', $tagId)
            //->where('games.eu_is_released', '1')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.title', 'asc');
            //->orderBy('games.rating_avg', 'desc')
            //->orderBy('games.eu_release_date', 'desc');

        $games = $games->get();
        return $games;
    }

    public function getUpcomingSwitchWeekly($daysLimit)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 0)
            ->whereNotNull('games.eu_release_date')
            ->whereRaw('eu_release_date < DATE_ADD(NOW(), INTERVAL ? DAY)', $daysLimit)
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.eshop_europe_order', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();

        return $games;
    }
}