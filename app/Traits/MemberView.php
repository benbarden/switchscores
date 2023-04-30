<?php


namespace App\Traits;


trait MemberView
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

    public function getBindings($pageTitle, $topTitle = 'Members')
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
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeGenericSubpage('Dashboard');
        return $this->getBindings($pageTitle);
    }

    public function getBindingsDashboardGenericSubpage($pageTitle, $tableSort = '')
    {
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeGenericSubpage($pageTitle);
        if ($tableSort) {
            $this->tableSort = $tableSort;
        }
        return $this->getBindings($pageTitle);
    }

    public function getBindingsQuickReviewsSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeQuickReviewsSubpage($pageTitle);
        return $this->getBindings($pageTitle);
    }

    public function getBindingsFeaturedGamesSubpage($pageTitle)
    {
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeFeaturedGamesSubpage($pageTitle);
        return $this->getBindings($pageTitle);
    }
}