<?php


namespace App\Domain\ViewBindings;


abstract class Base
{
    /**
     * @var array
     */
    protected $bindings = [];

    protected $pageTitle;
    protected $topTitle;
    protected $breadcrumbs;
    protected $tableSort;

    const KEY_PAGE_TITLE = 'PageTitle';
    const KEY_TOP_TITLE = 'TopTitle';
    const KEY_DATATABLES_SORT = 'jsInitialSort';

    const KEY_CRUMB_NAV = 'crumbNav';

    public function getBindings()
    {
        return $this->bindings;
    }

    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    public function setTopTitlePrefix($topTitle)
    {
        $this->topTitle = $topTitle.' - '.$this->pageTitle;
        return $this;
    }

    public function setTopTitleSuffix($topTitle)
    {
        $this->topTitle = $this->pageTitle.' - '.$topTitle;
        return $this;
    }

    public function setBreadcrumbs($crumbs)
    {
        $this->breadcrumbs = $crumbs;
        return $this;
    }

    public function setTableSort($sort)
    {
        $this->tableSort = $sort;
        return $this;
    }

    protected function generate()
    {
        if ($this->pageTitle) {
            $this->bindings[self::KEY_PAGE_TITLE] = $this->pageTitle;
        }

        if ($this->topTitle) {
            $this->bindings[self::KEY_TOP_TITLE] = $this->topTitle;
        }

        if ($this->breadcrumbs) {
            $this->bindings[self::KEY_CRUMB_NAV] = $this->breadcrumbs;
        }

        if ($this->tableSort) {
            $this->bindings[self::KEY_DATATABLES_SORT] = $this->tableSort;
        } else {
            // Always set the default sort - no harm in it being available
            $this->bindings[self::KEY_DATATABLES_SORT] = "[ 0, 'desc']";
        }

        return $this->bindings;
    }
}