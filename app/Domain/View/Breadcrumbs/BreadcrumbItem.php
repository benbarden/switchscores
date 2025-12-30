<?php

namespace App\Domain\View\Breadcrumbs;

final class BreadcrumbItem
{
    public function __construct(
        public string $text,
        public ?string $url = null,
    ) {}

    public function isLink(): bool
    {
        return $this->url !== null;
    }
}