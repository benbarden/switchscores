<?php

namespace App\Domain\Game\Repository;

use App\Enums\AmazonAffiliateStatus;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

class GameAffiliateRepository
{
    private function statusFieldForRegion(string $region): string
    {
        return match ($region) {
            'uk' => 'amazon_uk_status',
            default => 'amazon_us_status',
        };
    }

    private function countByStatus(string $region, AmazonAffiliateStatus $status): int
    {
        return Game::query()
            ->where(
                $this->statusFieldForRegion($region),
                $status->value
            )
            ->count();
    }

    private function getByStatus(string $region, AmazonAffiliateStatus $status, int $limit = 50): Collection
    {
        return Game::query()
            ->where(
                $this->statusFieldForRegion($region),
                $status->value
            )
            ->orderBy('rating_avg', 'desc')
            ->orderBy('review_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function countUnchecked(string $region = 'us'): int
    {
        return $this->countByStatus($region, AmazonAffiliateStatus::UNCHECKED);
    }

    public function countLinked(string $region = 'us'): int
    {
        return $this->countByStatus($region, AmazonAffiliateStatus::LINKED);
    }

    public function countNoProduct(string $region = 'us'): int
    {
        return $this->countByStatus($region, AmazonAffiliateStatus::NO_PRODUCT);
    }

    public function countIgnored(string $region = 'us'): int
    {
        return $this->countByStatus($region, AmazonAffiliateStatus::IGNORED);
    }

    public function getUnchecked(string $region = 'us', int $limit = 50): Collection
    {
        return $this->getByStatus($region, AmazonAffiliateStatus::UNCHECKED, $limit);
    }

    public function getLinked(string $region = 'us', int $limit = 50): Collection
    {
        return $this->getByStatus($region, AmazonAffiliateStatus::LINKED, $limit);
    }

    public function getNoProduct(string $region = 'us', int $limit = 50): Collection
    {
        return $this->getByStatus($region, AmazonAffiliateStatus::NO_PRODUCT, $limit);
    }

    public function getIgnored(string $region = 'us', int $limit = 50): Collection
    {
        return $this->getByStatus($region, AmazonAffiliateStatus::IGNORED, $limit);
    }
}