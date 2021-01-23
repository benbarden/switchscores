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

    // ***** Games collection ***** //

    public function addCollectionDashboard()
    {
        $crumbItem = ['url' => route('user.collection.landing'), 'text' => 'Games collection'];
        return $this->addCrumb($crumbItem);
    }

    public function makeCollectionSubpage($pageTitle)
    {
        return $this->addCollectionDashboard()
            ->addPageTitle($pageTitle)
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

    // ***** Database help ***** //

    public function addDatabaseHelpDashboard()
    {
        $crumbItem = ['url' => route('user.database-help.index'), 'text' => 'Database help'];
        return $this->addCrumb($crumbItem);
    }

    public function addDatabaseHelpGamesWithoutCategories()
    {
        $crumbItem = ['url' => route('user.database-help.games-without-categories'), 'text' => 'Games without categories'];
        return $this->addCrumb($crumbItem);
    }

    public function makeDatabaseHelpSubpage($pageTitle)
    {
        return $this->addDatabaseHelpDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeDatabaseHelpGamesWithoutCategoriesSubpage($pageTitle)
    {
        return $this->addDatabaseHelpDashboard()
            ->addDatabaseHelpGamesWithoutCategories()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

}