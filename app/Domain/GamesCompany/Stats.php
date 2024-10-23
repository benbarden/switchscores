<?php


namespace App\Domain\GamesCompany;

use App\Models\GamesCompany;

use Illuminate\Support\Facades\DB;

class Stats
{
    public function getWithoutEmails()
    {
        return GamesCompany::whereNull('email')->orderBy('id', 'desc')->get();
    }

    public function countWithoutEmails()
    {
        return GamesCompany::whereNull('email')->orderBy('id', 'desc')->count();
    }

    public function getWithoutWebsiteUrls()
    {
        return GamesCompany::whereNull('website_url')->orderBy('id', 'desc')->get();
    }

    public function countWithoutWebsiteUrls()
    {
        return GamesCompany::whereNull('website_url')->orderBy('id', 'desc')->count();
    }

    public function getWithoutTwitterIds()
    {
        return GamesCompany::whereNull('twitter_id')->orderBy('id', 'desc')->get();
    }

    public function countWithoutTwitterIds()
    {
        return GamesCompany::whereNull('twitter_id')->orderBy('id', 'desc')->count();
    }

    public function getDuplicateTwitterIds()
    {
        return DB::select('
            SELECT id, twitter_id, count(*) AS count
            FROM games_companies
            WHERE twitter_id IS NOT NULL
            GROUP BY twitter_id
            HAVING count(*) > 1
            ORDER BY twitter_id ASC
        ');
    }

    public function getDuplicateWebsiteUrls()
    {
        return DB::select('
            SELECT id, website_url, count(*) AS count
            FROM games_companies
            WHERE website_url IS NOT NULL
            GROUP BY website_url
            HAVING count(*) > 1
            ORDER BY website_url ASC
        ');
    }

    public function getWithTwitterIdList($twitterIdList)
    {
        return GamesCompany::whereIn('twitter_id', $twitterIdList)->orderBy('twitter_id', 'asc')->orderBy('id', 'asc')->get();
    }

    public function getWithWebsiteUrlList($websiteUrlList)
    {
        return GamesCompany::whereIn('website_url', $websiteUrlList)->orderBy('website_url', 'asc')->orderBy('id', 'asc')->get();
    }


}