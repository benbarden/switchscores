<?php

namespace App\Domain\Game\Repository;

use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

class GameCrawlRepository
{
    /**
     * Get games with a specific crawl status.
     */
    public function getByStatus(int $statusCode): Collection
    {
        return Game::query()
            ->where('last_crawl_status', $statusCode)
            ->active()
            ->orderBy('title')
            ->get();
    }

    /**
     * Get games with crawl status in a range (e.g., 300-399 for redirects).
     */
    public function getByStatusRange(int $min, int $max): Collection
    {
        return Game::query()
            ->whereBetween('last_crawl_status', [$min, $max])
            ->active()
            ->orderBy('title')
            ->get();
    }

    /**
     * Get games with crawl status >= threshold (e.g., >= 500 for server errors).
     */
    public function getByStatusMinimum(int $minimum): Collection
    {
        return Game::query()
            ->where('last_crawl_status', '>=', $minimum)
            ->active()
            ->orderBy('title')
            ->get();
    }

    /**
     * Get games returning 404 Not Found.
     */
    public function getStatus404(): Collection
    {
        return $this->getByStatus(404);
    }

    /**
     * Get games returning 410 Gone.
     */
    public function getStatus410(): Collection
    {
        return $this->getByStatus(410);
    }

    /**
     * Get games returning 3xx redirects.
     */
    public function getStatusRedirect(): Collection
    {
        return $this->getByStatusRange(300, 399);
    }

    /**
     * Get games returning 5xx server errors.
     */
    public function getStatusServerError(): Collection
    {
        return $this->getByStatusMinimum(500);
    }
}
