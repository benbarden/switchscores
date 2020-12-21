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

    public function getBindings($pageTitle, $topTitle, $tableSort = '')
    {
        $this->bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitleSuffix($topTitle)
            ->setBreadcrumbs($this->breadcrumbs);

        if ($tableSort) {
            $this->bindings = $this->bindings->setDatatablesSort($tableSort);
        } else {
            $this->bindings = $this->bindings->setDatatablesSortDefault();
        }

        return $this->bindings->getBindings();
    }

    public function getBindingsDashboard($pageTitle)
    {
        $topTitle = 'Members';
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeGenericSubpage('Dashboard');
        return $this->getBindings($pageTitle, $topTitle);
    }

    public function getBindingsDashboardGenericSubpage($pageTitle)
    {
        $topTitle = 'Members';
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeGenericSubpage($pageTitle);
        return $this->getBindings($pageTitle, $topTitle);
    }

    public function getBindingsCollectionSubpage($pageTitle)
    {
        $topTitle = 'Members';
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeCollectionSubpage($pageTitle);
        return $this->getBindings($pageTitle, $topTitle);
    }

    public function getBindingsDatabaseHelpSubpage($pageTitle)
    {
        $topTitle = 'Members';
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeDatabaseHelpSubpage($pageTitle);
        return $this->getBindings($pageTitle, $topTitle);
    }

    public function getBindingsDatabaseHelpGamesWithoutCategoriesSubpage($pageTitle)
    {
        $topTitle = 'Members';
        $this->breadcrumbs = $this->getServiceViewHelperMemberBreadcrumbs()->makeDatabaseHelpGamesWithoutCategoriesSubpage($pageTitle);
        return $this->getBindings($pageTitle, $topTitle);
    }
}