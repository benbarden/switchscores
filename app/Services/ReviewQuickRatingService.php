<?php


namespace App\Services;

use App\ReviewQuickRating;

class ReviewQuickRatingService
{
    public function find($id)
    {
        return ReviewQuickRating::find($id);
    }

    public function getAll()
    {
        $reviewList = ReviewQuickRating::orderBy('id', 'asc')->get();
        return $reviewList;
    }
}