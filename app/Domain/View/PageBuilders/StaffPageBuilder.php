<?php

namespace App\Domain\View\PageBuilders;

use App\Domain\View\PageView;
use App\Domain\View\Breadcrumbs\BreadcrumbNav;

final class StaffPageBuilder
{
    private const TITLE_SUFFIX = 'Staff';

    public function build(
        string $pageTitle,
        BreadcrumbNav $breadcrumbs,
        array $extraBindings = [],
        ?string $jsInitialSort = null,
        ?string $topTitleOverride = null,
    ): PageView {
        $topTitle = $topTitleOverride ?? $this->buildTopTitle($pageTitle);

        $bindings = array_merge([
            // identity
            'PageTitle' => $pageTitle,
            'TopTitle'  => $topTitle,

            // navigation
            'BreadcrumbNav' => $breadcrumbs->items(),

            // behaviour
            'jsInitialSort' => $jsInitialSort,

            // context
            'isStaff' => true,
        ], array_filter($extraBindings, fn ($v) => $v !== null));

        return new PageView(
            pageTitle: $pageTitle,
            topTitle: $topTitle,
            breadcrumbs: $breadcrumbs,
            bindings: $bindings
        );
    }

    private function buildTopTitle(string $pageTitle): string
    {
        return sprintf('%s - %s', $pageTitle, self::TITLE_SUFFIX);
    }
}