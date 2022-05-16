<?php


namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerOutreach;
use Illuminate\Support\Facades\DB;

class PartnerService
{
    /**
     * @deprecated
     */
    public function createReviewSite(
        $status, $name, $linkTitle, $websiteUrl, $twitterId, $ratingScale,
        $contactName, $contactEmail, $contactFormLink, $reviewCodeRegions,
        $reviewImportMethod
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
            'rating_scale' => $ratingScale,
            'contact_name' => $contactName,
            'contact_email' => $contactEmail,
            'contact_form_link' => $contactFormLink,
            'review_code_regions' => $reviewCodeRegions,
            'review_import_method' => $reviewImportMethod,
        ]);
    }

    /**
     * @deprecated
     */
    public function editReviewSite(
        Partner $partnerData, $status, $name, $linkTitle, $websiteUrl, $twitterId, $ratingScale,
        $contactName, $contactEmail, $contactFormLink, $reviewCodeRegions, $reviewImportMethod
    )
    {
        $values = [
            'status' => $status,
            'name' => $name,
            'link_title' => $linkTitle,
            'website_url' => $websiteUrl,
            'twitter_id' => $twitterId,
            'rating_scale' => $ratingScale,
            'contact_name' => $contactName,
            'contact_email' => $contactEmail,
            'contact_form_link' => $contactFormLink,
            'review_code_regions' => $reviewCodeRegions,
            'review_import_method' => $reviewImportMethod,
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
     * @return \App\Models\Partner
     */
    public function getByDomain($domainUrl)
    {
        return Partner::
            where('website_url', 'http://'.$domainUrl)
            ->orWhere('website_url', 'https://'.$domainUrl)
            ->first();
    }

    // ********************************************************** //

    /**
     * @deprecated
     */
    public function getActiveReviewSitesWithContactDetails()
    {
        $reviewSites = Partner::
            where('type_id', Partner::TYPE_REVIEW_SITE)
            ->where('status', Partner::STATUS_ACTIVE)
            ->whereNotNull('contact_email')->orWhereNotNull('contact_form_link')
            ->orderBy('name', 'asc')
            ->get();
        return $reviewSites;
    }

    /**
     * @deprecated
     */
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

    public function getGamesCompaniesWithoutWebsiteUrls()
    {
        return Partner::
            where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->whereNull('website_url')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function countGamesCompaniesWithoutWebsiteUrls()
    {
        return Partner::
            where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->whereNull('website_url')
            ->orderBy('id', 'desc')
            ->count();
    }

    public function getGamesCompaniesWithoutTwitterIds()
    {
        return Partner::
            where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->whereNull('twitter_id')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function countGamesCompaniesWithoutTwitterIds()
    {
        return Partner::
            where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->whereNull('twitter_id')
            ->orderBy('id', 'desc')
            ->count();
    }

    public function getGamesCompanyDuplicateTwitterIds()
    {
        return DB::select('
            SELECT id, twitter_id, count(*) AS count
            FROM partners
            WHERE type_id = ?
            AND twitter_id IS NOT NULL
            GROUP BY twitter_id
            HAVING count(*) > 1
            ORDER BY twitter_id ASC
        ', [Partner::TYPE_GAMES_COMPANY]);
    }

    public function getGamesCompanyDuplicateWebsiteUrls()
    {
        return DB::select('
            SELECT id, website_url, count(*) AS count
            FROM partners
            WHERE type_id = ?
            AND website_url IS NOT NULL
            GROUP BY website_url
            HAVING count(*) > 1
            ORDER BY website_url ASC
        ', [Partner::TYPE_GAMES_COMPANY]);
    }

    public function getGamesCompaniesWithTwitterIdList($twitterIdList)
    {
        return Partner::
            where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->whereIn('twitter_id', $twitterIdList)
            ->orderBy('twitter_id', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getGamesCompaniesWithWebsiteUrlList($websiteUrlList)
    {
        return Partner::
            where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->whereIn('website_url', $websiteUrlList)
            ->orderBy('website_url', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getGamesCompanyFromIdList($idList, $orderBy = '', $orderDir = '')
    {
        $partnerList = Partner::
            where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->whereIn('id', $idList);
        if ($orderBy) {
            $partnerList = $partnerList->orderBy($orderBy, $orderDir);
        }
        return $partnerList->get();
    }

    // ********************************************************** //

    public function getMergedGameList($gameDevList, $gamePubList)
    {
        $mergedGameList = [];
        $usedGameIds = [];

        if ($gameDevList && $gamePubList) {

            foreach ($gameDevList as $item) {
                $gameId = $item->id;
                $item->PartnerType = 'developer';
                $item->ExtraDetailLine = 'Developer';
                $mergedGameList[$gameId] = $item;
                $usedGameIds[] = $gameId;
            }
            foreach ($gamePubList as $item) {
                $gameId = $item->id;
                if (in_array($gameId, $usedGameIds)) {
                    $mergedGameList[$gameId]->PartnerType = 'dev/pub';
                    $mergedGameList[$gameId]->ExtraDetailLine = 'Developer/Publisher';
                } else {
                    $item->PartnerType = 'publisher';
                    $item->ExtraDetailLine = 'Publisher';
                    $mergedGameList[] = $item;
                }
            }

        } elseif ($gameDevList) {

            $mergedGameList = $gameDevList;
            foreach ($gameDevList as $item) {
                $item->PartnerType = 'developer';
                $item->ExtraDetailLine = 'Developer';
                $mergedGameList[] = $item;
            }

        } elseif ($gamePubList) {

            $mergedGameList = $gamePubList;
            foreach ($gamePubList as $item) {
                $item->PartnerType = 'publisher';
                $item->ExtraDetailLine = 'Publisher';
                $mergedGameList[] = $item;
            }

        }

        return $mergedGameList;
    }

    // Outreach targets
    public function getPublishersWithUnrankedGames()
    {
        return Partner::select(
            'partners.id', 'partners.name', 'partners.link_title',
            'games.id AS game_id', 'games.title AS game_title',
            'games.link_title AS game_link_title', 'games.review_count', 'games.rating_avg')
            ->join('game_publishers', 'game_publishers.publisher_id', '=', 'partners.id')
            ->join('games', 'games.id', '=', 'game_publishers.game_id')
            ->where('partners.type_id', Partner::TYPE_GAMES_COMPANY)
            ->where('games.review_count', '<', 3)
            ->whereNull('partners.last_outreach_id')
            ->orderBy('partners.id', 'asc')
            ->orderBy('games.id', 'asc')
            ->get();
    }

    // Outreach targets
    public function getDevelopersWithUnrankedGames()
    {
        return Partner::select(
            'partners.id', 'partners.name', 'partners.link_title',
            'games.id AS game_id', 'games.title AS game_title',
            'games.link_title AS game_link_title', 'games.review_count', 'games.rating_avg')
            ->join('game_developers', 'game_developers.developer_id', '=', 'partners.id')
            ->join('games', 'games.id', '=', 'game_developers.game_id')
            ->where('partners.type_id', Partner::TYPE_GAMES_COMPANY)
            ->where('games.review_count', '<', 3)
            ->whereNull('partners.last_outreach_id')
            ->orderBy('partners.id', 'asc')
            ->orderBy('games.id', 'asc')
            ->get();
    }

}