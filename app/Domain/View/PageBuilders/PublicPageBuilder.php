<?php

namespace App\Domain\View\PageBuilders;

final class PublicPageBuilder extends AbstractPageBuilder
{
    protected function titleSuffix(): string
    {
        return 'Switch Scores';
    }

    protected function contextBindings(): array
    {
        return ['isPublic' => true];
    }
}
