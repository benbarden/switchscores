<?php


namespace App\Services;

use App\ReviewSite;


class ReviewSiteService
{
    public function create($name, $url, $linkTitle, $active, $ratingScale)
    {
        ReviewSite::create([
            'name' => $name,
            'url' => $url,
            'link_title' => $linkTitle,
            'active' => $active,
            'rating_scale' => $ratingScale,
        ]);
    }

    public function find($id)
    {
        return ReviewSite::find($id);
    }

    public function getByDomain($domainUrl)
    {
        $reviewSite = ReviewSite::
            where('url', 'http://'.$domainUrl)
            ->orWhere('url', 'https://'.$domainUrl)
            ->first();
        return $reviewSite;
    }

    public function getByLinkTitle($linkTitle)
    {
        $reviewSite = ReviewSite::
            where('link_title', $linkTitle)
            ->where('active', 'Y')
            ->first();
        return $reviewSite;
    }

    public function getAll()
    {
        $reviewSites = ReviewSite::orderBy('name', 'asc')->get();
        return $reviewSites;
    }
}