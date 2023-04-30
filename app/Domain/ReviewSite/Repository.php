<?php

namespace App\Domain\ReviewSite;

use App\Models\ReviewSite;


class Repository
{
    public function find($id)
    {
        return ReviewSite::find($id);
    }

    public function getByLinkTitle($linkTitle)
    {
        return ReviewSite::where('link_title', $linkTitle)->first();
    }

    public function getAll()
    {
        return ReviewSite::orderBy('name', 'asc')->get();
    }

    public function getActive()
    {
        return ReviewSite::orderBy('name', 'asc')->get();
    }

    public function getActiveScraper()
    {
        return ReviewSite::where('status', ReviewSite::STATUS_ACTIVE)
            ->where('review_import_method', ReviewSite::REVIEW_IMPORT_BY_SCRAPER)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getNoRecentReviews()
    {
        return ReviewSite::where('status', ReviewSite::STATUS_NO_RECENT_REVIEWS)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getActiveWithContactDetails()
    {
        return ReviewSite::where('status', ReviewSite::STATUS_ACTIVE)
            ->whereNotNull('contact_email')->orWhereNotNull('contact_form_link')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getByDomain($domainUrl)
    {
        return ReviewSite::where('website_url', 'http://'.$domainUrl)
            ->orWhere('website_url', 'https://'.$domainUrl)
            ->first();
    }

}