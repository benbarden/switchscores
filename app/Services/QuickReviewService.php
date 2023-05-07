<?php


namespace App\Services;

use App\Models\QuickReview;

class QuickReviewService
{
    public function create(
        $userId, $gameId, $reviewScore, $reviewBody
    )
    {
        return QuickReview::create([
            'user_id' => $userId,
            'game_id' => $gameId,
            'review_score' => $reviewScore,
            'review_body' => $reviewBody,
            'item_status' => QuickReview::STATUS_PENDING,
        ]);
    }

    public function edit(
        QuickReview $quickReviewData,
        $gameId, $reviewScore, $reviewBody
    )
    {
        $values = [
            'game_id' => $gameId,
            'review_score' => $reviewScore,
            'review_body' => $reviewBody,
        ];

        $quickReviewData->fill($values);
        $quickReviewData->save();
    }

    public function editStatus(
        QuickReview $quickReviewData, $itemStatus
    )
    {
        $values = [
            'item_status' => $itemStatus,
        ];

        $quickReviewData->fill($values);
        $quickReviewData->save();
    }

    public function delete($reviewId)
    {
        QuickReview::where('id', $reviewId)->delete();
    }

    public function find($id)
    {
        return QuickReview::find($id);
    }

    public function getAll($limit = null)
    {
        $reviewList = QuickReview::orderBy('id', 'desc');
        if ($limit) {
            $reviewList = $reviewList->limit($limit);
        }
        $reviewList = $reviewList->get();
        return $reviewList;
    }

    public function getStatusList()
    {
        $statuses = [];
        $statuses[] = ['id' => QuickReview::STATUS_PENDING, 'title' => 'Pending'];
        $statuses[] = ['id' => QuickReview::STATUS_ACTIVE, 'title' => 'Active'];
        $statuses[] = ['id' => QuickReview::STATUS_INACTIVE, 'title' => 'Inactive'];
        return $statuses;
    }

    public function getByStatus($status)
    {
        $reviewList = QuickReview::
            where('item_status', $status)
            ->orderBy('id', 'desc');
        $reviewList = $reviewList->get();
        return $reviewList;
    }

    public function getAllByUser($userId)
    {
        $reviewList = QuickReview::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        return $reviewList;
    }

    /**
     * @deprecated
     */
    public function getAllByUserGameIdList($userId)
    {
        $reviewList = QuickReview::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->pluck('game_id');
        return $reviewList;
    }

    public function getLatestActive($limit = 10)
    {
        $reviewList = QuickReview::orderBy('created_at', 'desc')
            ->where('item_status', QuickReview::STATUS_ACTIVE)
            ->limit($limit)
            ->get();
        return $reviewList;
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

    public function getByGameAndUser($gameId, $userId)
    {
        $gameReview = QuickReview::where('game_id', $gameId)
            ->where('user_id', $userId)
            ->first();
        return $gameReview;
    }
}