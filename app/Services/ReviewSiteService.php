<?php


namespace App\Services;

use App\ReviewSite;


class ReviewSiteService
{
    public function create($name, $url, $active, $ratingScale)
    {
        ReviewSite::create([
            'name' => $name,
            'url' => $url,
            'active' => $active,
            'rating_scale' => $ratingScale,
        ]);
    }

    public function find($id)
    {
        return ReviewSite::find($id);
    }

    public function getAll()
    {
        $reviewSites = ReviewSite::orderBy('name', 'asc')->get();
        return $reviewSites;
    }
}