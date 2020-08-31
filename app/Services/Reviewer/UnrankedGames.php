<?php

namespace App\Services\Reviewer;

use Illuminate\Support\Facades\DB;

class UnrankedGames
{
    public function getReviewedBySite($siteId)
    {
        $reviewedBySite = DB::select('
            SELECT g.release_year, count(*) AS count
            FROM games g
            JOIN review_links rl ON g.id = rl.game_id
            WHERE rl.site_id = ?
            AND g.release_year IS NOT NULL
            AND g.release_year != 0
            GROUP BY g.release_year
            ORDER BY g.release_year ASC
        ', [$siteId]);

        return $reviewedBySite;
    }

    public function getTotalUnranked()
    {
        $unrankedStats = DB::select('
            SELECT release_year, count(*) AS count
            FROM games
            WHERE game_rank IS NULL
            AND release_year IS NOT NULL
            AND release_year != 0
            GROUP BY release_year
            ORDER BY release_year ASC
        ');

        return $unrankedStats;
    }

    public function getUnrankedReviewedBySite($siteId)
    {
        $unrankedReviewedBySite = DB::select('
            SELECT release_year, count(*) AS count
            FROM games
            WHERE game_rank IS NULL
            AND release_year IS NOT NULL
            AND release_year != 0
            AND id IN (
                SELECT g.id FROM games g
                JOIN review_links rl ON g.id = rl.game_id
                AND rl.site_id = ?
                ORDER BY g.id ASC
            )
            GROUP BY release_year
            ORDER BY release_year ASC
        ', [$siteId]);

        return $unrankedReviewedBySite;
    }
}