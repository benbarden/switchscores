<?php


namespace App\Services\ViewHelper;

use App\Models\DataSource;

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
        $crumbItem = ['url' => route('staff.games-title-hash.list'), 'text' => 'Game title hashes'];
        return $this->addCrumb($crumbItem);
    }

    public function makeGamesTitleHashesSubPage($pageTitle)
    {
        return $this->addGamesDashboard()
            ->addGamesTitleHashesList()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    private function addGamesFeaturedGamesList()
    {
        $crumbItem = ['url' => route('staff.games.featured-games.list'), 'text' => 'Featured games'];
        return $this->addCrumb($crumbItem);
    }

    public function makeGamesFeaturedGamesSubPage($pageTitle)
    {
        return $this->addGamesDashboard()
            ->addGamesFeaturedGamesList()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    // ***** Reviews ***** //

    private function addReviewsDashboard()
    {
        $crumbItem = ['url' => route('staff.reviews.dashboard'), 'text' => 'Reviews'];
        return $this->addCrumb($crumbItem);
    }

    private function addReviewsLinkList()
    {
        $crumbItem = ['url' => route('staff.reviews.link.list'), 'text' => 'Review links'];
        return $this->addCrumb($crumbItem);
    }

    private function addReviewsQuickReviewsList()
    {
        $crumbItem = ['url' => route('staff.reviews.quick-reviews.list'), 'text' => 'Quick reviews'];
        return $this->addCrumb($crumbItem);
    }

    private function addReviewsCampaignsIndex()
    {
        $crumbItem = ['url' => route('staff.reviews.campaigns'), 'text' => 'Campaigns'];
        return $this->addCrumb($crumbItem);
    }

    private function addReviewsFeedItemsIndex()
    {
        $crumbItem = ['url' => route('staff.reviews.feed-items.list'), 'text' => 'Feed items'];
        return $this->addCrumb($crumbItem);
    }

    private function addReviewsFeedImportsIndex()
    {
        $crumbItem = ['url' => route('staff.reviews.feed-imports.list'), 'text' => 'Feed imports'];
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

    public function makeReviewsLinkListSubPage($pageTitle)
    {
        return $this->addReviewsDashboard()
            ->addReviewsLinkList()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeReviewsQuickReviewsSubPage($pageTitle)
    {
        return $this->addReviewsDashboard()
            ->addReviewsQuickReviewsList()
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

    public function makeReviewsFeedItemsSubPage($pageTitle)
    {
        return $this->addReviewsDashboard()
            ->addReviewsFeedItemsIndex()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    public function makeReviewsFeedImportsSubPage($pageTitle)
    {
        return $this->addReviewsDashboard()
            ->addReviewsFeedImportsIndex()
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

    private function addReviewSitesList()
    {
        $crumbItem = ['url' => route('staff.reviews.site.list'), 'text' => 'Review sites'];
        return $this->addCrumb($crumbItem);
    }

    public function makeReviewSitesSubPage($pageTitle)
    {
        return $this->addPartnersDashboard()
            ->addReviewSitesList()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    private function addFeedLinksList()
    {
        $crumbItem = ['url' => route('staff.partners.feed-links.list'), 'text' => 'Feed links'];
        return $this->addCrumb($crumbItem);
    }

    public function makeFeedLinksSubPage($pageTitle)
    {
        return $this->addPartnersDashboard()
            ->addFeedLinksList()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    private function addGamesCompaniesList()
    {
        $crumbItem = ['url' => route('staff.partners.games-company.list'), 'text' => 'Games companies'];
        return $this->addCrumb($crumbItem);
    }

    public function makeGamesCompaniesSubPage($pageTitle)
    {
        return $this->addPartnersDashboard()
            ->addGamesCompaniesList()
            ->addPageTitle($pageTitle)
            ->getBreadcrumbs();
    }

    private function addPartnersOutreach()
    {
        $crumbItem = ['url' => route('staff.partners.outreach.list'), 'text' => 'Outreach'];
        return $this->addCrumb($crumbItem);
    }

    public function makePartnersOutreachSubPage($pageTitle)
    {
        return $this->addPartnersDashboard()
            ->addPartnersOutreach()
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
}