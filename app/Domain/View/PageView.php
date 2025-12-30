<?php

namespace App\Domain\View;

use App\Domain\View\Breadcrumbs\BreadcrumbNav;

final class PageView
{
    public function __construct(
        public string $pageTitle,
        public string $topTitle,
        public BreadcrumbNav $breadcrumbs,
        public array $bindings,
    ) {}
}