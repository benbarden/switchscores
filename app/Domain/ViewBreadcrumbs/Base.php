<?php


namespace App\Domain\ViewBreadcrumbs;


abstract class Base
{
    /**
     * @var array
     */
    private $breadcrumbs = [];

    const KEY_URL = 'url';
    const KEY_TEXT = 'text';

    protected function addCrumb($crumbItem)
    {
        array_push($this->breadcrumbs, $crumbItem);
        return $this;
    }

    protected function addPageTitle($pageTitle)
    {
        $crumbItem = ['text' => $pageTitle];
        return $this->addCrumb($crumbItem);
    }

    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    public function addTitleAndReturn($pageTitle)
    {
        return $this->addPageTitle($pageTitle)->getBreadcrumbs();
    }
}