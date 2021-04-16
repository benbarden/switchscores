<?php


namespace App\Domain\GameLists;


use Illuminate\Support\Facades\DB;
use App\Game;

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
            ->where('games.eu_is_released', '1')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.eu_release_date', 'desc');

        $games = $games->get();
        return $games;
    }
}