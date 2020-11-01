<?php

namespace App\Services\ViewHelper;

class StaffBreadcrumbs
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

    // ***** Staff - General ***** //

    public function makeStaffDashboard($pageTitle)
    {
        return $this->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Games ***** //

    private function addGamesDashboard()
    {
        $crumbItem = ['url' => route('staff.games.dashboard'), 'text' => 'Games'];
        return $this->addCrumb($crumbItem);
    }

    public function makeGamesSubPage($pageTitle)
    {
        return $this->addGamesDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Reviews ***** //

    private function addReviewsDashboard()
    {
        $crumbItem = ['url' => route('staff.reviews.dashboard'), 'text' => 'Reviews'];
        return $this->addCrumb($crumbItem);
    }

    private function addReviewsCampaignsIndex()
    {
        $crumbItem = ['url' => route('staff.reviews.campaigns'), 'text' => 'Campaigns'];
        return $this->addCrumb($crumbItem);
    }

    private function addReviewsUnrankedByReviewCount()
    {
        $crumbItem = ['url' => route('staff.reviews.unranked.review-count-landing'), 'text' => 'Unranked: By review count'];
        return $this->addCrumb($crumbItem);
    }

    private function addReviewsUnrankedByReleaseYear()
    {
        $crumbItem = ['url' => route('staff.reviews.unranked.release-year-landing'), 'text' => 'Unranked: By release year'];
        return $this->addCrumb($crumbItem);
    }

    public function makeReviewsSubPage($pageTitle)
    {
        return $this->addReviewsDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeReviewsCampaignsSubPage($pageTitle)
    {
        return $this->addReviewsDashboard()
            ->addReviewsCampaignsIndex()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeReviewsUnrankedByReviewCountSubPage($pageTitle)
    {
        return $this->addReviewsDashboard()
            ->addReviewsUnrankedByReviewCount()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeReviewsUnrankedByReleaseYearSubPage($pageTitle)
    {
        return $this->addReviewsDashboard()
            ->addReviewsUnrankedByReleaseYear()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Categorisation ***** //

    private function addCategorisationDashboard()
    {
        $crumbItem = ['url' => route('staff.categorisation.dashboard'), 'text' => 'Categorisation'];
        return $this->addCrumb($crumbItem);
    }

    public function makeCategorisationSubPage($pageTitle)
    {
        return $this->addCategorisationDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Partners ***** //

    private function addPartnersDashboard()
    {
        $crumbItem = ['url' => route('staff.partners.dashboard'), 'text' => 'Partners'];
        return $this->addCrumb($crumbItem);
    }

    public function makePartnersSubPage($pageTitle)
    {
        return $this->addPartnersDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Data quality ***** //

    private function addDataQualityDashboard()
    {
        $crumbItem = ['url' => route('staff.data-quality.dashboard'), 'text' => 'Data quality'];
        return $this->addCrumb($crumbItem);
    }

    public function makeDataQualitySubPage($pageTitle)
    {
        return $this->addDataQualityDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

}