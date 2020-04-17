<?php

namespace App\Services\ViewHelper;

class Bindings
{
    private $bindings = [];

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
        $this->bindings[self::KEY_PAGE_TITLE] = $pageTitle;
        return $this;
    }

    public function setTopTitlePrefix($topTitle)
    {
        $pageTitle = $this->bindings[self::KEY_PAGE_TITLE];
        $this->bindings[self::KEY_TOP_TITLE] = $topTitle.' - '.$pageTitle;
        return $this;
    }

    public function setTopTitleSuffix($topTitle)
    {
        $pageTitle = $this->bindings[self::KEY_PAGE_TITLE];
        $this->bindings[self::KEY_TOP_TITLE] = $pageTitle.' - '.$topTitle;
        return $this;
    }

    public function setBreadcrumbs($crumbs)
    {
        $this->bindings[self::KEY_CRUMB_NAV] = $crumbs;
        return $this;
    }

    public function setDatatablesSort($sort)
    {
        $this->bindings[self::KEY_DATATABLES_SORT] = $sort;
        return $this;
    }

    public function setDatatablesSortDefault()
    {
        return $this->setDatatablesSort("[ 0, 'desc']");
    }
}