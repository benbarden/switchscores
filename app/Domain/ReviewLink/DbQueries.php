<?php


namespace App\Domain\ReviewLink;


use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function recentlyRanked($dayInterval = 14, $limit = null)
    {
        $limitSql = '';
        if ($limit) {
            $limit = (int) $limit;
            if ($limit) {
                $limitSql = 'LIMIT '.$limit;
            }
        }
        $reviewLinks = DB::select('
            SELECT g.*, count(rl.id) AS recent_review_count
            FROM review_links rl
            JOIN games g ON rl.game_id = g.id
            JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rl.review_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND g.review_count > 2
            AND g.format_digital != "De-listed"
            GROUP BY rl.game_id
            HAVING g.review_count - count(rl.id) < 3
            ORDER BY g.rating_avg DESC, g.eu_release_date DESC, g.id ASC
        '.$limitSql, [$dayInterval]);

        return $reviewLinks;
    }
}