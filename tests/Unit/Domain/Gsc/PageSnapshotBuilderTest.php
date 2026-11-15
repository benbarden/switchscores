<?php

namespace Tests\Unit\Domain\Gsc;

use App\Domain\Gsc\Snapshot\PageSnapshotBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Which Insights section a GSC page URL belongs to.
 */
class PageSnapshotBuilderTest extends TestCase
{
    public static function urls(): array
    {
        $site = 'https://www.switchscores.com';

        return [
            // Site pages: general pages, console pages and the bare browse landings
            [$site.'/', 'site'],
            [$site.'/about', 'site'],
            [$site.'/buyers-guide', 'site'],
            [$site.'/buyers-guide/us', 'site'],
            [$site.'/buyers-guide/uk', 'site'],
            [$site.'/c/switch-1', 'site'],
            [$site.'/c/switch-2', 'site'],
            [$site.'/c/switch-1/new-releases', 'site'],
            [$site.'/c/switch-1/upcoming', 'site'],
            [$site.'/c/switch-1/2025', 'site'],
            [$site.'/games/on-sale', 'site'],
            [$site.'/reviews', 'site'],
            [$site.'/news', 'site'],
            [$site.'/browse/category', 'site'],
            [$site.'/browse/tag', 'site'],
            [$site.'/browse/series', 'site'],
            // Top rated
            [$site.'/c/switch-1/top-rated', 'top_rated'],
            [$site.'/c/switch-1/top-rated/by-year/2026', 'top_rated'],
            [$site.'/c/switch-2/top-rated/all-time', 'top_rated'],
            // Games
            [$site.'/games/1/the-legend-of-zelda-breath-of-the-wild', 'game'],
            [$site.'/games/1', 'game'],
            [$site.'/switch-2/games/15011/mario-kart-world', 'game'],
            // Categories: only /browse/category/...
            [$site.'/browse/category/rpg', 'category'],
            [$site.'/browse/category/rpg/list', 'category'],
            // Other families, not shown
            [$site.'/browse/tag/anime', 'other'],
            [$site.'/browse/series/pokemon/list?console=switch-1', 'other'],
            [$site.'/browse/collection/lego', 'other'],
            [$site.'/c/switch-1/category/rpg', 'other'],
            [$site.'/news/2026-11-01/some-article', 'other'],
            [$site.'/reviews/site/switchup', 'other'],
            [$site.'/partners/games-company/nintendo', 'other'],
        ];
    }

    /**
     * @dataProvider urls
     */
    public function testPageType(string $url, string $expected)
    {
        $this->assertSame($expected, (new PageSnapshotBuilder())->detectPageType($url), $url);
    }

    /**
     * The site pages fetch's exclude regex must leave out exactly the non-site pages, or the
     * fetch spends its row limit on them. PHP's regex stands in for GSC's RE2 here; the
     * pattern uses only syntax both share.
     *
     * @dataProvider urls
     */
    public function testSiteFetchExcludesEverythingButSitePages(string $url, string $expected)
    {
        $excluded = (bool) preg_match('#'.PageSnapshotBuilder::SITE_FETCH_EXCLUDE_REGEX.'#', $url);
        $this->assertSame($expected !== 'site', $excluded, $url);
    }
}
