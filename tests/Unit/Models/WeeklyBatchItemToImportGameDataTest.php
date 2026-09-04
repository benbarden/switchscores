<?php

namespace Tests\Unit\Models;

use App\Models\WeeklyBatchItem;
use Tests\TestCase;

/**
 * GameImporter works from an ImportGameData value object rather than a batch item, so
 * this mapping is the only thing tying the weekly batch to the importer (#135).
 *
 * The field names differ on both sides of it, which is exactly where a silent mistake
 * would live: `publisher_normalised` becomes `publisher` (the importer looks the company
 * up by that name, and the raw store name often will not match), and `nintendo_url`
 * becomes `url`.
 */
class WeeklyBatchItemToImportGameDataTest extends TestCase
{
    private function makeItem(array $overrides = []): WeeklyBatchItem
    {
        return new WeeklyBatchItem(array_merge([
            'title'                => 'Some Ordinary Game',
            'console'              => 'switch-2',
            'release_date'         => '2026-09-04',
            'price_gbp'            => 29.99,
            'players'              => '1-4',
            'nintendo_url'         => 'https://www.nintendo.com/en-gb/some-ordinary-game',
            'packshot_url'         => 'https://example.test/packshot.jpg',
            'publisher_raw'        => 'SOME PUBLISHER LTD.',
            'publisher_normalised' => 'Some Publisher',
            'category'             => 'Action',
            'collection'           => 'arcade-archives',
            'description'          => 'A game.',
        ], $overrides));
    }

    public function testMapsEveryFieldTheImporterReads()
    {
        $data = $this->makeItem()->toImportGameData();

        $this->assertEquals('Some Ordinary Game', $data->title);
        $this->assertEquals('switch-2', $data->consoleSlug);
        $this->assertEquals('2026-09-04', $data->releaseDate);
        $this->assertEquals(29.99, $data->priceGbp);
        $this->assertEquals('1-4', $data->players);
        $this->assertEquals('https://www.nintendo.com/en-gb/some-ordinary-game', $data->url);
        $this->assertEquals('https://example.test/packshot.jpg', $data->packshotUrl);
        $this->assertEquals('Action', $data->category);
        $this->assertEquals('arcade-archives', $data->collection);
        $this->assertEquals('A game.', $data->description);
    }

    public function testPublisherUsesTheNormalisedName()
    {
        $data = $this->makeItem()->toImportGameData();

        $this->assertEquals('Some Publisher', $data->publisher);
    }

    public function testHandlesAnItemWithNothingOptionalSet()
    {
        $data = $this->makeItem([
            'release_date'         => null,
            'price_gbp'            => null,
            'players'              => null,
            'nintendo_url'         => null,
            'packshot_url'         => null,
            'publisher_normalised' => null,
            'category'             => null,
            'collection'           => null,
            'description'          => null,
        ])->toImportGameData();

        $this->assertEquals('Some Ordinary Game', $data->title);
        $this->assertNull($data->releaseDate);
        $this->assertNull($data->priceGbp);
        $this->assertNull($data->url);
        $this->assertNull($data->publisher);
        $this->assertNull($data->description);
    }

    public function testPriceIsAFloatNotTheDecimalCastString()
    {
        $data = $this->makeItem(['price_gbp' => 7.5])->toImportGameData();

        $this->assertIsFloat($data->priceGbp);
        $this->assertEquals(7.50, $data->priceGbp);
    }
}
