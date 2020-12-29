<?php


namespace App\Traits;


use App\DataSource;

trait StaffView
{
    /**
     * @var array
     */
    private $breadcrumbs = [];

    /**
     * @var array
     */
    private $bindings = [];

    /**
     * @var string
     */
    private $pageTitle;

    /**
     * @var string
     */
    private $tableSort;

    public function getBindings($pageTitle, $topTitle = 'Staff')
    {
        $this->bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitleSuffix($topTitle)
            ->setBreadcrumbs($this->breadcrumbs);

        if ($this->tableSort) {
            $this->bindings = $this->bindings->setDatatablesSort($this->tableSort);
        } else {
            $this->bindings = $this->bindings->setDatatablesSortDefault();
        }

        return $this->bindings->getBindings();
    }

    public function getBindingsDashboard($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeGenericSubpage('Dashboard');
        return $this->getBindings($pageTitle);
    }

    public function getBindingsDashboardGenericSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeGenericSubpage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    // ***** Games ***** //

    public function getBindingsGamesSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeGamesSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    public function getBindingsGamesDetailSubpage($pageTitle, $gameId)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeGamesDetailSubPage($pageTitle, $gameId);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsGamesTitleHashesSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeGamesTitleHashesSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    // ***** Reviews ***** //

    public function getBindingsReviewsSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    // ***** Categorisation ***** //

    public function getBindingsCategorisationSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeCategorisationSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    public function getBindingsCategorisationCategoriesSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeCategorisationCategoriesSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    public function getBindingsCategorisationTagSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeCategorisationTagSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    // ***** News ***** //

    public function getBindingsNewsSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeNewsSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsNewsListSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeNewsListSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsNewsCategoriesSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeNewsCategoriesSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    // ***** Partners ***** //

    public function getBindingsPartnersSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makePartnersSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    public function getBindingsReviewSitesSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewSitesSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsGamesCompaniesSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeGamesCompaniesSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsPartnersOutreachSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makePartnersOutreachSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    // ***** Data sources ***** //

    public function getBindingsDataSourcesSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeDataSourcesSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    public function getBindingsDataSourcesListRawSubpage($pageTitle, DataSource $dataSource)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeDataSourcesListRawSubPage($pageTitle, $dataSource);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsDataSourcesNintendoCoUkUnlinkedItemsSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeDataSourcesNintendoCoUkUnlinkedItemsSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsDataSourcesWikipediaUnlinkedItemsSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeDataSourcesWikipediaUnlinkedItemsSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    // ***** Data quality ***** //

    public function getBindingsDataQualitySubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeDataQualitySubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsDataQualityCategorySubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeDataQualityCategorySubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    // ***** Stats ***** //

    public function getBindingsStatsSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeStatsSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }
}