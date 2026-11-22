<?php

namespace App\Domain\Game\Repository;

use App\Enums\AmazonAffiliateStatus;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

class GameAffiliateRepository
{
    /**
     * A store set to No product or Ignored is saved with no ASIN or link: the buy button is built
     * from the ASIN or link whatever the status says, so one left behind would still show.
     */
    public function updateAffiliateData(
        Game $game,
        ?string $amazonUKAsin,
        ?string $amazonUKLink,
        ?string $amazonUKStatus,
        ?string $amazonUSAsin,
        ?string $amazonUSLink,
        ?string $amazonUSStatus,
    ): void {
        if ($this->statusMeansNoLink($amazonUKStatus)) {
            $amazonUKAsin = null;
            $amazonUKLink = null;
        }
        if ($this->statusMeansNoLink($amazonUSStatus)) {
            $amazonUSAsin = null;
            $amazonUSLink = null;
        }

        $game->amazon_uk_asin = $amazonUKAsin;
        $game->amazon_uk_link = $amazonUKLink;
        $game->amazon_uk_status = $amazonUKStatus;
        $this->clearUsCheckIfAsinChanges($game, $amazonUSAsin);
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
        if ($region === 'us') {
            $this->clearUsCheckIfAsinChanges($game, $asin);
        }
        $game->{$this->asinFieldForRegion($region)} = $asin;
        $game->{$this->statusFieldForRegion($region)} = AmazonAffiliateStatus::LINKED->value;

        $game->save();
    }

    public function markRegionNoProduct(Game $game, string $region): void
    {
        if ($region === 'us') {
            $this->clearUsCheckIfAsinChanges($game, null);
        }
        $game->{$this->asinFieldForRegion($region)} = null;
        $game->{$region === 'uk' ? 'amazon_uk_link' : 'amazon_us_link'} = null;
        $game->{$this->statusFieldForRegion($region)} = AmazonAffiliateStatus::NO_PRODUCT->value;

        $game->save();
    }

    public function hasDirectLink(Game $game, string $region): bool
    {
        return (bool) ($region === 'uk'
            ? ($game->amazon_uk_asin ?: $game->amazon_uk_link)
            : ($game->amazon_us_asin ?: $game->amazon_us_link));
    }

    /**
     * A new US ASIN hasn't been checked yet, so the last result no longer applies: the game drops
     * off the link check list straight away, and the next nightly run checks the new one.
     */
    private function clearUsCheckIfAsinChanges(Game $game, ?string $newAsin): void
    {
        if ($newAsin !== $game->amazon_us_asin) {
            $game->amazon_us_check_result = null;
            $game->amazon_us_checked_at = null;
        }
    }

    private function statusMeansNoLink(?string $status): bool
    {
        return in_array($status, [AmazonAffiliateStatus::NO_PRODUCT->value, AmazonAffiliateStatus::IGNORED->value], true);
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