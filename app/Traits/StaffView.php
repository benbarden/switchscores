<?php


namespace App\Traits;

use App\Models\DataSource;

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

    /**
     * @deprecated
     */
    public function getBindings($pageTitle, $topTitle = 'Staff')
    {
        $this->bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitleSuffix($topTitle);

        if ($this->breadcrumbs) {
            $this->bindings = $this->bindings->setBreadcrumbs($this->breadcrumbs);
        }

        if ($this->tableSort) {
            $this->bindings = $this->bindings->setDatatablesSort($this->tableSort);
        } else {
            $this->bindings = $this->bindings->setDatatablesSortDefault();
        }

        return $this->bindings->getBindings();
    }

    /**
     * @deprecated
     */
    public function setTableSort($tableSort)
    {
        $this->tableSort = $tableSort;
    }

    // ***** Reviews ***** //

    /**
     * @deprecated
     */
    public function getBindingsReviewsSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    /**
     * @deprecated
     */
    public function getBindingsReviewsLinkListSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsLinkListSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    /**
     * @deprecated
     */
    public function getBindingsReviewsQuickReviewsSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsQuickReviewsSubPage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    /**
     * @deprecated
     */
    public function getBindingsReviewsUnrankedByReleaseYearSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsUnrankedByReleaseYearSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    /**
     * @deprecated
     */
    public function getBindingsReviewsUnrankedByReviewCountSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsUnrankedByReviewCountSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    // ***** Data sources ***** //

    /**
     * @deprecated
     */
    public function getBindingsDataSourcesSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeDataSourcesSubPage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }
}