<?php

namespace App\Domain\View\Breadcrumbs;

final class BreadcrumbNav
{
    /**
     * @param BreadcrumbItem[] $items
     */
    public function __construct(
        private array $items,
    ) {}

    /**
     * @return BreadcrumbItem[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public static function fromArray(array $items): self
    {
        return new self($items);
    }
}