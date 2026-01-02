<?php

namespace App\Domain\View\PageBuilders;

use App\Domain\View\Breadcrumbs\BreadcrumbNav;
use App\Domain\View\PageView;

abstract class AbstractPageBuilder
{
    final public function build(
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

            // context (subclass-defined)
            ...$this->contextBindings(),
        ], array_filter($extraBindings, fn ($v) => $v !== null));

        return new PageView(
            pageTitle: $pageTitle,
            topTitle: $topTitle,
            breadcrumbs: $breadcrumbs,
            bindings: $bindings
        );
    }

    final protected function buildTopTitle(string $pageTitle): string
    {
        return sprintf('%s - %s', $pageTitle, $this->titleSuffix());
    }

    /** e.g. 'Staff', 'Members', 'Switch Scores' */
    abstract protected function titleSuffix(): string;

    /** e.g. ['isStaff' => true] */
    abstract protected function contextBindings(): array;
}
