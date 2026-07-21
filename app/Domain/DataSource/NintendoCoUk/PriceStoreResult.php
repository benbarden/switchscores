<?php

namespace App\Domain\DataSource\NintendoCoUk;

/**
 * Counts from a PriceStore::store() call, for import run reporting.
 */
class PriceStoreResult
{
    private int $created = 0;
    private int $changed = 0;
    private int $unchanged = 0;

    public function recordCreated(): void
    {
        $this->created++;
    }

    public function recordChanged(): void
    {
        $this->changed++;
    }

    public function recordUnchanged(): void
    {
        $this->unchanged++;
    }

    public function created(): int
    {
        return $this->created;
    }

    public function changed(): int
    {
        return $this->changed;
    }

    public function unchanged(): int
    {
        return $this->unchanged;
    }

    public function total(): int
    {
        return $this->created + $this->changed + $this->unchanged;
    }
}
