<?php

namespace Tests\Feature\Seo;

use Tests\TestCase;

/**
 * Regression cover for the 2026-09-04 canonical consolidation.
 *
 * The legacy /c/{console}/... browse URLs used to 301 to /browse/...?console={slug}, but a
 * ?console= URL declares the console-agnostic page as its canonical. That made every legacy
 * hit a two-hop journey for Google and minted an "alternative page with proper canonical tag"
 * URL along the way. These redirects must land on the canonical directly.
 */
class LegacyConsoleRedirectTest extends TestCase
{
    public static function legacyBrowseUrlProvider(): array
    {
        return [
            'category list, switch 1' => ['/c/switch-1/category/city-building/list', '/browse/category/city-building/list'],
            'category list, switch 2' => ['/c/switch-2/category/city-building/list', '/browse/category/city-building/list'],
            'category page'           => ['/c/switch-1/category/city-building', '/browse/category/city-building'],
            'category landing'        => ['/c/switch-1/category', '/browse/category'],
            'tag page'                => ['/c/switch-1/tag/deck-building', '/browse/tag/deck-building'],
            'tag landing'             => ['/c/switch-1/tag', '/browse/tag'],
            'series page'             => ['/c/switch-1/series/rune-factory', '/browse/series/rune-factory'],
            'series landing'          => ['/c/switch-1/series', '/browse/series'],
            'collection landing'      => ['/c/switch-1/collection', '/browse/collection'],
        ];
    }

    /**
     * @dataProvider legacyBrowseUrlProvider
     */
    public function testLegacyConsoleUrlRedirectsToCanonicalWithoutConsoleParam(string $legacyUrl, string $expectedPath)
    {
        $response = $this->get($legacyUrl);

        $response->assertStatus(301);
        $response->assertRedirect(url($expectedPath));

        $location = $response->headers->get('Location');
        $this->assertStringNotContainsString('console=', $location,
            'The redirect must not append ?console=, which points at a non-canonical URL.');
    }
}
