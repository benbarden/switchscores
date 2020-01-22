<?php


namespace App\Services;

use App\PartnerReview;

class PartnerReviewService
{
    public function create(
        $userId, $siteId, $gameId, $itemUrl, $itemDate, $itemRating
    )
    {
        return PartnerReview::create([
            'user_id' => $userId,
            'site_id' => $siteId,
            'game_id' => $gameId,
            'item_url' => $itemUrl,
            'item_date' => $itemDate,
            'item_rating' => $itemRating,
            'item_status' => PartnerReview::STATUS_PENDING,
        ]);
    }

    public function edit(
        PartnerReview $partnerReview,
        $gameId, $quickRating, $reviewScore, $reviewBody
    )
    {
        $values = [
            'game_id' => $gameId,
            'quick_rating' => $quickRating,
            'review_score' => $reviewScore,
            'review_body' => $reviewBody,
        ];

        $partnerReview->fill($values);
        $partnerReview->save();
    }

    public function editStatus(
        PartnerReview $partnerReviewData, $itemStatus
    )
    {
        $values = [
            'item_status' => $itemStatus,
        ];

        $partnerReviewData->fill($values);
        $partnerReviewData->save();
    }

    public function find($id)
    {
        return PartnerReview::find($id);
    }

    public function getAll($limit = null)
    {
        $reviewList = PartnerReview::orderBy('id', 'desc');
        if ($limit) {
            $reviewList = $reviewList->limit($limit);
        }
        $reviewList = $reviewList->get();
        return $reviewList;
    }

    public function getStatusList()
    {
        $statuses = [];
        $statuses[] = ['id' => PartnerReview::STATUS_PENDING, 'title' => 'Pending'];
        $statuses[] = ['id' => PartnerReview::STATUS_ACTIVE, 'title' => 'Active'];
        $statuses[] = ['id' => PartnerReview::STATUS_INACTIVE, 'title' => 'Inactive'];
        return $statuses;
    }

    public function getByStatus($status)
    {
        $reviewList = PartnerReview::
            where('item_status', $status)
            ->orderBy('id', 'desc');
        $reviewList = $reviewList->get();
        return $reviewList;
    }

    public function getAllBySite($siteId)
    {
        $reviewList = PartnerReview::where('site_id', $siteId)
            ->orderBy('id', 'desc')
            ->get();
        return $reviewList;
    }

    public function getLatestNaturalOrder($limit = 10)
    {
        $reviewList = PartnerReview::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        return $reviewList;
    }

    public function getByGame($gameId)
    {
        $gameReviews = PartnerReview::select('partner_reviews.*')
            ->where('game_id', $gameId)
            ->orderBy('partner_reviews.created_at', 'desc')
            ->get();
        return $gameReviews;
    }

    public function getByGameAndSite($gameId, $siteId)
    {
        $gameReview = PartnerReview::where('game_id', $gameId)
            ->where('site_id', $siteId)
            ->first();
        return $gameReview;
    }

    public function getByUser($userId)
    {
        $gameReviews = PartnerReview::select('partner_reviews.*')
            ->where('user_id', $userId)
            ->orderBy('partner_reviews.created_at', 'desc')
            ->get();
        return $gameReviews;
    }
}