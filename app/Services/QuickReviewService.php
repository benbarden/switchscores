<?php


namespace App\Services;

use App\Models\QuickReview;

class QuickReviewService
{

    public function getStatusList()
    {
        $statuses = [];
        $statuses[] = ['id' => QuickReview::STATUS_PENDING, 'title' => 'Pending'];
        $statuses[] = ['id' => QuickReview::STATUS_ACTIVE, 'title' => 'Active'];
        $statuses[] = ['id' => QuickReview::STATUS_INACTIVE, 'title' => 'Inactive'];
        return $statuses;
    }

    public function getByGame($gameId)
    {
        $gameReviews = QuickReview::select('quick_reviews.*')
            ->where('game_id', $gameId)
            ->orderBy('quick_reviews.created_at', 'desc')
            ->get();
        return $gameReviews;
    }

    public function getActiveByGame($gameId)
    {
        $gameReviews = QuickReview::select('quick_reviews.*')
            ->where('game_id', $gameId)
            ->where('item_status', QuickReview::STATUS_ACTIVE)
            ->orderBy('quick_reviews.created_at', 'desc')
            ->get();
        return $gameReviews;
    }
}