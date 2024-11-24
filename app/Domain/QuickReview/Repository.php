<?php


namespace App\Domain\QuickReview;

use App\Models\QuickReview;

class Repository
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
        $quickReviewData->item_status = $itemStatus;
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

    public function byGameActive($gameId)
    {
        return QuickReview::where('game_id', $gameId)->where('item_status', QuickReview::STATUS_ACTIVE)->get();
    }

    public function byUserGameIdList($userId)
    {
        return QuickReview::where('user_id', $userId)->orderBy('id', 'desc')->pluck('game_id');
    }
}