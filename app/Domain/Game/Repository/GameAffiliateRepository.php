<?php

namespace App\Domain\Game\Repository;

use App\Enums\AmazonAffiliateStatus;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

class GameAffiliateRepository
{
    public function updateAffiliateData(
        Game $game,
        ?string $amazonUKAsin,
        ?string $amazonUKLink,
        ?string $amazonUKStatus,
        ?string $amazonUSAsin,
        ?string $amazonUSLink,
        ?string $amazonUSStatus,
    ): void {
        $game->amazon_uk_asin = $amazonUKAsin;
        $game->amazon_uk_link = $amazonUKLink;
        $game->amazon_uk_status = $amazonUKStatus;
        $game->amazon_us_asin = $amazonUSAsin;
        $game->amazon_us_link = $amazonUSLink;
        $game->amazon_us_status = $amazonUSStatus;

        $game->save();
    }

    /**
     * Saves one region only, so a save for US can't change UK.
     */
    public function saveRegionAsin(Game $game, string $region, string $asin): void
    {
        $game->{$this->asinFieldForRegion($region)} = $asin;
        $game->{$this->statusFieldForRegion($region)} = AmazonAffiliateStatus::LINKED->value;

        $game->save();
    }

    public function markRegionNoProduct(Game $game, string $region): void
    {
        $game->{$this->statusFieldForRegion($region)} = AmazonAffiliateStatus::NO_PRODUCT->value;

        $game->save();
    }

    public function hasDirectLink(Game $game, string $region): bool
    {
        return (bool) ($region === 'uk'
            ? ($game->amazon_uk_asin ?: $game->amazon_uk_link)
            : ($game->amazon_us_asin ?: $game->amazon_us_link));
    }

    private function asinFieldForRegion(string $region): string
    {
        return match ($region) {
            'uk' => 'amazon_uk_asin',
            default => 'amazon_us_asin',
        };
    }

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
            ->orderBy('review_count', 'desc')
            ->orderBy('rating_avg', 'desc')
            ->orderBy('eu_release_date', 'asc')
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