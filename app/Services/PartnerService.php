<?php


namespace App\Services;

use App\Partner;

class PartnerService
{
    public function createReviewSite(
        $status, $name, $linkTitle, $websiteUrl, $twitterId,
        $feedUrl, $feedUrlPrefix, $ratingScale,
        $allowHistoricContent, $titleMatchRulePattern, $titleMatchIndex
    )
    {
        $typeId = Partner::TYPE_REVIEW_SITE;

        Partner::create([
            'type_id' => $typeId,
            'status' => $status,
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
            'feed_url' => $feedUrl,
            'feed_url_prefix' => $feedUrlPrefix,
            'rating_scale' => $ratingScale,
            'allow_historic_content' => $allowHistoricContent,
            'title_match_rule_pattern' => $titleMatchRulePattern,
            'title_match_index' => $titleMatchIndex,
        ]);
    }

    public function editReviewSite(
        Partner $partnerData, $status, $name, $linkTitle, $websiteUrl, $twitterId,
        $feedUrl, $feedUrlPrefix, $ratingScale,
        $allowHistoricContent, $titleMatchRulePattern, $titleMatchIndex
    )
    {
        $values = [
            'status' => $status,
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
            'feed_url' => $feedUrl,
            'feed_url_prefix' => $feedUrlPrefix,
            'rating_scale' => $ratingScale,
            'allow_historic_content' => $allowHistoricContent,
            'title_match_rule_pattern' => $titleMatchRulePattern,
            'title_match_index' => $titleMatchIndex,
        ];

        $partnerData->fill($values);
        $partnerData->save();
    }

    // ********************************************************** //

    public function editGamesCompany(
        Partner $partnerData, $name, $linkTitle, $websiteUrl, $twitterId
    )
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
        ];

        $partnerData->fill($values);
        $partnerData->save();
    }

    public function deleteGamesCompany($id)
    {
        Partner::where('id', $id)->delete();
    }

    // ********************************************************** //

    /**
     * @param $id
     * @return Partner
     */
    public function find($id)
    {
        return Partner::find($id);
    }

    /**
     * @param $name
     * @return Partner
     */
    public function getByName($name)
    {
        return Partner::where('name', $name)->first();
    }

    /**
     * @param $linkTitle
     * @return Partner
     */
    public function getByLinkTitle($linkTitle)
    {
        return Partner::
            where('link_title', $linkTitle)
            ->where('status', Partner::STATUS_ACTIVE)
            ->first();
    }

    /**
     * @param $domainUrl
     * @return Partner
     */
    public function getByDomain($domainUrl)
    {
        return Partner::
            where('website_url', 'http://'.$domainUrl)
            ->orWhere('website_url', 'https://'.$domainUrl)
            ->first();
    }

    // ********************************************************** //

    public function getAllForUserAssignment()
    {
        return Partner::
            where('status', Partner::STATUS_ACTIVE)
            ->orderBy('type_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    // ********************************************************** //

    public function getAllReviewSites()
    {
        return Partner::where('type_id', Partner::TYPE_REVIEW_SITE)->orderBy('name', 'asc')->get();
    }

    public function getActiveReviewSites()
    {
        return Partner::
            where('type_id', Partner::TYPE_REVIEW_SITE)
            ->where('status', Partner::STATUS_ACTIVE)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getInactiveReviewSites()
    {
        return Partner::
            where('type_id', Partner::TYPE_REVIEW_SITE)
            ->where('status', Partner::STATUS_INACTIVE)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getReviewSiteFeedUrls()
    {
        $reviewSites = Partner::
            where('type_id', Partner::TYPE_REVIEW_SITE)
            ->where('status', Partner::STATUS_ACTIVE)
            ->whereNotNull('feed_url')
            ->orderBy('name', 'asc')
            ->get();
        return $reviewSites;
    }

    public function getReviewSitesWithRecentReviews($days = 30)
    {
        return Partner::
            where('type_id', Partner::TYPE_REVIEW_SITE)
            ->where('status', Partner::STATUS_ACTIVE)
            ->whereRaw('last_review_date between date_sub(NOW(), INTERVAL ? DAY) and now()', $days)
            ->orderBy('name', 'asc')
            ->get();
    }

    // ********************************************************** //

    public function getAllGamesCompanies()
    {
        return Partner::where('type_id', Partner::TYPE_GAMES_COMPANY)->orderBy('name', 'asc')->get();
    }
}