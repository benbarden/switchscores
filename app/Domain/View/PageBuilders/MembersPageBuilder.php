<?php

namespace App\Domain\View\PageBuilders;

final class MembersPageBuilder extends AbstractPageBuilder
{
    protected function titleSuffix(): string
    {
        return 'Members';
    }

    protected function contextBindings(): array
    {
        return ['isMember' => true];
    }
}
