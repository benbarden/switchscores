<?php


namespace App\Domain\ReviewLink;

use App\Models\ReviewLink;
use App\Models\ReviewSite;

class Repository
{
    public function create(
        $gameId, $siteId, $url, $ratingOriginal, $ratingNormalised, $reviewDate,
        $reviewType = ReviewLink::TYPE_IMPORTED, $desc = null, $userId = null
    )
    {
        return ReviewLink::create([
            'game_id' => $gameId,
            'site_id' => $siteId,
            'url' => $url,
            'rating_original' => $ratingOriginal,
            'rating_normalised' => $ratingNormalised,
            'review_date' => $reviewDate,
            'review_type' => $reviewType,
            'description' => $desc,
            'user_id' => $userId,
        ]);
    }

    public function edit(
        ReviewLink $reviewLinkData, $gameId, $siteId, $url, $ratingOriginal, $ratingNormalised,
        $reviewDate, $desc = null
    )
    {
        $values = [
            'game_id' => $gameId,
            'site_id' => $siteId,
            'url' => $url,
            'rating_original' => $ratingOriginal,
            'rating_normalised' => $ratingNormalised,
            'review_date' => $reviewDate,
            'description' => $desc,
        ];

        $reviewLinkData->fill($values);
        $reviewLinkData->save();
    }

    public function delete($linkId)
    {
        ReviewLink::where('id', $linkId)->delete();
    }

    public function find($linkId)
    {
        return ReviewLink::find($linkId);
    }

    public function recentlyAdded($limit)
    {
        return ReviewLink::orderBy('id', 'desc')->limit($limit)->get();
    }

    public function bySite($siteId)
    {
        return ReviewLink::where('site_id', $siteId)->get();
    }

    public function bySiteGameIdList($siteId)
    {
        return ReviewLink::where('site_id', $siteId)->orderBy('id', 'desc')->pluck('game_id');
    }

    public function byGame($gameId)
    {
        return ReviewLink::where('game_id', $gameId)->get();
    }

    public function byUrl($url)
    {
        return ReviewLink::where('url', $url)->first();
    }

    public function byGameAndSite($gameId, $siteId)
    {
        return ReviewLink::where('game_id', $gameId)->where('site_id', $siteId)->first();
    }

    public function getNormalisedRating($ratingOriginal, $ratingScale)
    {
        $normalisedScaleLimit = 10;

        if ($ratingScale != $normalisedScaleLimit) {
            $scaleMultiple = $normalisedScaleLimit / $ratingScale;
            $ratingNormalised = round($ratingOriginal * $scaleMultiple, 2);
        } else {
            $ratingNormalised = $ratingOriginal;
        }

        return $ratingNormalised;
    }

    public function getLatestBySite($siteId)
    {
        return ReviewLink::where('site_id', $siteId)
            ->orderBy('review_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }
}