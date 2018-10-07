<?php


namespace App\Services;

use App\ReviewUser;

class ReviewUserService
{
    public function create(
        $userId, $gameId, $quickRating, $reviewScore, $reviewBody
    )
    {
        return ReviewUser::create([
            'user_id' => $userId,
            'game_id' => $gameId,
            'quick_rating' => $quickRating,
            'review_score' => $reviewScore,
            'review_body' => $reviewBody,
            'item_status' => ReviewUser::STATUS_PENDING,
        ]);
    }

    public function edit(
        ReviewUser $reviewUserData,
        $gameId, $quickRating, $reviewScore, $reviewBody
    )
    {
        $values = [
            'game_id' => $gameId,
            'quick_rating' => $quickRating,
            'review_score' => $reviewScore,
            'review_body' => $reviewBody,
        ];

        $reviewUserData->fill($values);
        $reviewUserData->save();
    }

    public function editStatus(
        ReviewUser $reviewUserData, $itemStatus
    )
    {
        $values = [
            'item_status' => $itemStatus,
        ];

        $reviewUserData->fill($values);
        $reviewUserData->save();
    }

    public function find($id)
    {
        return ReviewUser::find($id);
    }

    public function getAll($limit = null)
    {
        $reviewList = ReviewUser::orderBy('id', 'desc');
        if ($limit) {
            $reviewList = $reviewList->limit($limit);
        }
        $reviewList = $reviewList->get();
        return $reviewList;
    }

    public function getStatusList()
    {
        $statuses = [];
        $statuses[] = ['id' => ReviewUser::STATUS_PENDING, 'title' => 'Pending'];
        $statuses[] = ['id' => ReviewUser::STATUS_ACTIVE, 'title' => 'Active'];
        $statuses[] = ['id' => ReviewUser::STATUS_INACTIVE, 'title' => 'Inactive'];
        return $statuses;
    }

    public function getByStatus($status)
    {
        $reviewList = ReviewUser::
            where('item_status', $status)
            ->orderBy('id', 'desc');
        $reviewList = $reviewList->get();
        return $reviewList;
    }

    public function getAllByUser($userId)
    {
        $reviewList = ReviewUser::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        return $reviewList;
    }

    public function getLatestNaturalOrder($limit = 10)
    {
        $reviewList = ReviewUser::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        return $reviewList;
    }

    public function getByGame($gameId)
    {
        $gameReviews = ReviewUser::select('review_user.*', 'review_quick_rating.rating_desc')
            ->leftJoin('review_quick_rating', 'review_user.quick_rating', '=', 'review_quick_rating.id')
            ->where('game_id', $gameId)
            ->orderBy('review_user.created_at', 'desc')
            ->get();
        return $gameReviews;
    }

    public function getByGameAndUser($gameId, $userId)
    {
        $gameReview = ReviewUser::where('game_id', $gameId)
            ->where('user_id', $userId)
            ->first();
        return $gameReview;
    }
}