<?php


namespace App\Services;

use App\ReviewSite;


class ReviewSiteService
{
    public function create($name, $linkTitle, $url, $feedUrl, $active, $ratingScale)
    {
        ReviewSite::create([
            'name' => $name,
            'link_title' => $linkTitle,
            'url' => $url,
            'feed_url' => $feedUrl,
            'active' => $active,
            'rating_scale' => $ratingScale,
        ]);
    }

    public function edit(
        ReviewSite $reviewSiteData,
        $name, $linkTitle, $url, $feedUrl, $active, $ratingScale
    )
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
            'url' => $url,
            'feed_url' => $feedUrl,
            'active' => $active,
            'rating_scale' => $ratingScale,
        ];

        $reviewSiteData->fill($values);
        $reviewSiteData->save();
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

    public function getActive()
    {
        $reviewSites = ReviewSite::
            where('active', 'Y')
            ->orderBy('name', 'asc')->get();
        return $reviewSites;
    }

    public function getFeedUrls()
    {
        $reviewSites = ReviewSite::
            whereNotNull('feed_url')
            ->get();
        return $reviewSites;
    }
}