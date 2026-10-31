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

    public function byUser($userId)
    {
        return QuickReview::where('user_id', $userId)->orderBy('id', 'desc')->get();
    }

    public function byUserVisible($userId)
    {
        return QuickReview::where('user_id', $userId)
            ->whereIn('item_status', [QuickReview::STATUS_PENDING, QuickReview::STATUS_ACTIVE])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function byUserGameIdList($userId)
    {
        return QuickReview::where('user_id', $userId)->orderBy('id', 'desc')->pluck('game_id');
    }

    public function byStatus($status)
    {
        return QuickReview::where('item_status', $status)->orderBy('id', 'desc')->get();
    }

    public function getLatestActive($limit = 10)
    {
        return QuickReview::where('item_status', QuickReview::STATUS_ACTIVE)->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * A random selection of recent reviews, capped per member so one busy reviewer
     * can't fill the list.
     */
    public function getRandomRecentActive($limit = 5, $maxPerMember = 2, $months = 6)
    {
        $reviews = QuickReview::where('item_status', QuickReview::STATUS_ACTIVE)
            ->where('created_at', '>=', now()->subMonths($months))
            ->inRandomOrder()
            ->get();

        $picked = collect();
        $countByMember = [];

        foreach ($reviews as $review) {
            $memberCount = $countByMember[$review->user_id] ?? 0;
            if ($memberCount >= $maxPerMember) {
                continue;
            }

            $countByMember[$review->user_id] = $memberCount + 1;
            $picked->push($review);

            if ($picked->count() >= $limit) {
                break;
            }
        }

        return $picked;
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
        $statuses[] = ['id' => QuickReview::STATUS_REJECTED, 'title' => 'Rejected'];
        return $statuses;
    }
}