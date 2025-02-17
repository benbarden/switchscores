<?php


namespace App\Domain\UserGamesCollection;


use App\Models\QuickReview;
use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function getCategoryBreakdown($userId)
    {
        return DB::select('
            SELECT g.*, c.name, count(*) AS count
            FROM user_games_collection ugc
            JOIN games g ON g.id = ugc.game_id
            JOIN categories c ON g.category_id = c.id
            WHERE ugc.user_id = ?
            GROUP BY g.category_id
            ORDER BY count(*) DESC
        ', [$userId]);
    }

    public function nextToReviewInCollection($userId)
    {
        return DB::select('
            SELECT g.id FROM games g
            JOIN user_games_collection ugc on g.id = ugc.game_id
            WHERE ugc.user_id = ? AND ugc.play_status IN (?, ?)
            AND g.id NOT IN (
                select qr.game_id from quick_reviews qr
                where qr.item_status IN (?, ?)
                and qr.user_id = ?
            )
            ORDER BY RAND() LIMIT 1
        ', [$userId,
            PlayStatus::PLAY_STATUS_COMPLETED,
            PlayStatus::PLAY_STATUS_ENDLESS,
            QuickReview::STATUS_ACTIVE, QuickReview::STATUS_PENDING,
            $userId]);
    }
}