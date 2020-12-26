<?php

namespace App\Services\ViewHelper;

use App\DataSource;

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

    public function makeGenericSubpage($pageTitle)
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

    private function addGamesDetail($gameId)
    {
        $crumbItem = ['url' => route('staff.games.detail', ['gameId' => $gameId]), 'text' => 'Detail'];
        return $this->addCrumb($crumbItem);
    }

    public function makeGamesDetailSubPage($pageTitle, $gameId)
    {
        return $this->addGamesDashboard()
            ->addGamesDetail($gameId)
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    private function addGamesTitleHashesList()
    {
        $crumbItem = ['url' => route('admin.games-title-hash.list'), 'text' => 'Game title hashes'];
        return $this->addCrumb($crumbItem);
    }

    public function makeGamesTitleHashesSubPage($pageTitle)
    {
        return $this->addGamesDashboard()
            ->addGamesTitleHashesList()
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

    private function addCategorisationCategoriesDashboard()
    {
        $crumbItem = ['url' => route('staff.categorisation.category.list'), 'text' => 'Categories'];
        return $this->addCrumb($crumbItem);
    }

    private function addCategorisationTagDashboard()
    {
        $crumbItem = ['url' => route('staff.categorisation.tag.list'), 'text' => 'Tags'];
        return $this->addCrumb($crumbItem);
    }

    public function makeCategorisationSubPage($pageTitle)
    {
        return $this->addCategorisationDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeCategorisationCategoriesSubPage($pageTitle)
    {
        return $this->addCategorisationDashboard()
            ->addCategorisationCategoriesDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeCategorisationTagSubPage($pageTitle)
    {
        return $this->addCategorisationDashboard()
            ->addCategorisationTagDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** News ***** //

    private function addNewsDashboard()
    {
        $crumbItem = ['url' => route('staff.news.dashboard'), 'text' => 'News'];
        return $this->addCrumb($crumbItem);
    }

    private function addNewsList()
    {
        $crumbItem = ['url' => route('staff.news.list'), 'text' => 'News list'];
        return $this->addCrumb($crumbItem);
    }

    private function addNewsCategoryList()
    {
        $crumbItem = ['url' => route('staff.news.category.list'), 'text' => 'News categories'];
        return $this->addCrumb($crumbItem);
    }

    public function makeNewsSubPage($pageTitle)
    {
        return $this->addNewsDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeNewsListSubPage($pageTitle)
    {
        return $this->addNewsDashboard()
            ->addNewsList()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeNewsCategoriesSubPage($pageTitle)
    {
        return $this->addNewsDashboard()
            ->addNewsCategoryList()
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

    // ***** Data sources ***** //

    private function addDataSourcesDashboard()
    {
        $crumbItem = ['url' => route('staff.data-sources.dashboard'), 'text' => 'Data sources'];
        return $this->addCrumb($crumbItem);
    }

    public function makeDataSourcesSubPage($pageTitle)
    {
        return $this->addDataSourcesDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    private function addDataSourcesListRaw(DataSource $dataSource)
    {
        $crumbItem = [
            'url' => route('staff.data-sources.list-raw', ['sourceId' => $dataSource->id]),
            'text' => $dataSource->name
        ];
        return $this->addCrumb($crumbItem);
    }

    public function makeDataSourcesListRawSubPage($pageTitle, DataSource $dataSource)
    {
        return $this->addDataSourcesDashboard()
            ->addDataSourcesListRaw($dataSource)
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    private function addDataSourcesNintendoCoUkUnlinkedItems()
    {
        $crumbItem = [
            'url' => route('staff.data-sources.nintendo-co-uk.unlinked'),
            'text' => 'Nintendo.co.uk API - Unlinked items'
        ];
        return $this->addCrumb($crumbItem);
    }

    public function makeDataSourcesNintendoCoUkUnlinkedItemsSubPage($pageTitle)
    {
        return $this->addDataSourcesDashboard()
            ->addDataSourcesNintendoCoUkUnlinkedItems()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    private function addDataSourcesWikipediaUnlinkedItems()
    {
        $crumbItem = [
            'url' => route('staff.data-sources.wikipedia.unlinked'),
            'text' => 'Wikipedia - Unlinked items'
        ];
        return $this->addCrumb($crumbItem);
    }

    public function makeDataSourcesWikipediaUnlinkedItemsSubPage($pageTitle)
    {
        return $this->addDataSourcesDashboard()
            ->addDataSourcesWikipediaUnlinkedItems()
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

    private function addDataQualityCategoryDashboard()
    {
        $crumbItem = ['url' => route('staff.data-quality.category.dashboard'), 'text' => 'Categories'];
        return $this->addCrumb($crumbItem);
    }

    public function makeDataQualityCategorySubPage($pageTitle)
    {
        return $this->addDataQualityDashboard()
            ->addDataQualityCategoryDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Stats ***** //

    private function addStatsDashboard()
    {
        $crumbItem = ['url' => route('staff.stats.dashboard'), 'text' => 'Stats'];
        return $this->addCrumb($crumbItem);
    }

    public function makeStatsSubPage($pageTitle)
    {
        return $this->addStatsDashboard()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

}