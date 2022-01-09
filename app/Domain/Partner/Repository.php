<?php


namespace App\Domain\Partner;

use App\Models\Partner;

class Repository
{
    /**
     * @param $id
     * @return \App\Models\Partner
     */
    public function find($id)
    {
        return Partner::find($id);
    }

    public function searchGamesCompany($name)
    {
        return Partner::where('name', 'LIKE', '%'.$name.'%')
            ->where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function reviewSitesActive()
    {
        return Partner::where('type_id', Partner::TYPE_REVIEW_SITE)
            ->where('status', Partner::STATUS_ACTIVE)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function reviewSitesActiveForScraper()
    {
        return Partner::where('type_id', Partner::TYPE_REVIEW_SITE)
            ->where('status', Partner::STATUS_ACTIVE)
            ->where('review_import_method', Partner::REVIEW_IMPORT_BY_SCRAPER)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function reviewSitesActiveRecent($days = 30)
    {
        return Partner::where('type_id', Partner::TYPE_REVIEW_SITE)
            ->where('status', Partner::STATUS_ACTIVE)
            ->whereRaw('last_review_date between date_sub(NOW(), INTERVAL ? DAY) and now()', $days)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function gamesCompanies()
    {
        return Partner::where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->orderBy('name', 'asc')
            ->get();
    }
}