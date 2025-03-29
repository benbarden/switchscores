<?php

namespace App\Domain\CampaignGame;

use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function countRankedGames($campaignId)
    {
        $gamesList = DB::select('
            SELECT count(*) AS count FROM campaign_games cg
            JOIN games g ON cg.game_id = g.id
            WHERE cg.campaign_id = ?
            AND g.review_count > 2
        ', [$campaignId]);

        return $gamesList[0]->count;
    }
}