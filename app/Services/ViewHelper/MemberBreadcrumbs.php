<?php

namespace App\Services\ViewHelper;

class MemberBreadcrumbs
{
    private $breadcrumbs = [];

    const KEY_URL = 'url';
    const KEY_TEXT = 'text';

    // ***** Standard methods ***** //

    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    private function addCrumb($crumbItem)
    {
        array_push($this->breadcrumbs, $crumbItem);
        return $this;
    }

    private function addPageTitle($pageTitle)
    {
        $crumbItem = ['text' => $pageTitle];
        return $this->addCrumb($crumbItem);
    }

    // ***** Member - General ***** //

    public function makeGenericSubpage($pageTitle)
    {
        return $this->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Quick reviews ***** //

    public function addQuickReviewsList()
    {
        $crumbItem = ['url' => route('user.quick-reviews.list'), 'text' => 'Quick reviews'];
        return $this->addCrumb($crumbItem);
    }

    public function makeQuickReviewsSubpage($pageTitle)
    {
        return $this->addQuickReviewsList()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Featured games ***** //

    public function addFeaturedGames()
    {
        // No list page, so just include the text
        $crumbItem = ['text' => 'Featured games'];
        return $this->addCrumb($crumbItem);
    }

    public function makeFeaturedGamesSubpage($pageTitle)
    {
        return $this->addFeaturedGames()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

}