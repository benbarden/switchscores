<?php


namespace App\Services;

use App\Partner;
use App\PartnerOutreach;
use Illuminate\Support\Facades\DB;

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

    public function editOutreach(
        Partner $partner, PartnerOutreach $lastOutreach
    )
    {
        $values = [
            'last_outreach_id' => $lastOutreach->id,
        ];

        $partner->fill($values)->save();
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

    // ********************************************************** //
    // MIGRATION WORK
    // ********************************************************** //

    // Step 1a. Exact matches
    public function getGameDevelopersForMigration()
    {
        return DB::select('
            SELECT g.id AS game_id, 
            g.title AS game_title, 
            g.developer, 
            p.id AS partner_id, 
            p.name AS partner_name
            FROM games g
            LEFT JOIN partners p ON g.developer = p.name
            WHERE p.id IS NOT NULL
            ORDER BY g.id ASC
        ');
    }

    // Step 1b. Exact matches
    public function getGamePublishersForMigration()
    {
        return DB::select('
            SELECT g.id AS game_id, 
            g.title AS game_title, 
            g.publisher, 
            p.id AS partner_id, 
            p.name AS partner_name
            FROM games g
            LEFT JOIN partners p ON g.publisher = p.name
            WHERE p.id IS NOT NULL
            ORDER BY g.id ASC
        ');
    }

    // Step 2a. List the partners that don't match
    public function getUnmatchedGameDevelopers()
    {
        return DB::select('
            select g.developer, count(*) AS count from games g
            left join partners p on g.developer = p.name
            where g.developer is not null and p.id is null
            group by g.developer
            order by g.developer asc
        ');
    }

    // Step 2b. List the partners that don't match
    public function getUnmatchedGamePublishers()
    {
        return DB::select('
            select g.publisher, count(*) AS count from games g
            left join partners p on g.publisher = p.name
            where g.publisher is not null and p.id is null
            group by g.publisher
            order by g.publisher asc
        ');
    }

    // Outreach targets
    public function getPublishersWithUnrankedGames()
    {
        return DB::select('
            select p.id, p.name, g.id AS game_id, g.title AS game_title, g.link_title AS game_link_title, g.review_count, g.rating_avg
            from partners p
            join game_publishers gp on p.id = gp.publisher_id
            join games g on g.id = gp.game_id
            where p.type_id = ?
            and g.review_count < 3
            and p.last_outreach_id is null
            order by p.id asc, g.id asc
        ', [Partner::TYPE_GAMES_COMPANY]);
    }

    // Outreach targets
    public function getDevelopersWithUnrankedGames()
    {
        return DB::select('
            select p.id, p.name, g.id AS game_id, g.title AS game_title, g.link_title AS game_link_title, g.review_count, g.rating_avg
            from partners p
            join game_developers gd on p.id = gd.developer_id
            join games g on g.id = gd.game_id
            where p.type_id = ?
            and g.review_count < 3
            and p.last_outreach_id is null
            order by p.id asc, g.id asc
        ', [Partner::TYPE_GAMES_COMPANY]);
    }

}