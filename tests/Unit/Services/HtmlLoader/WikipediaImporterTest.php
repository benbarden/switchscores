<?php

namespace Tests\Unit\Services\HtmlLoader;

use App\Factories\Tests\GameImportRuleWikipediaFactory;
use Illuminate\Support\Collection;
use Tests\TestCase;

use App\Game;
use App\GameReleaseDate;
use App\FeedItemGame;
use App\Services\HtmlLoader\Wikipedia\Importer as WikiImporter;

use App\Factories\Tests\FeedItemGameFactory;
use App\Factories\Tests\GameReleaseDateFactory;

class WikipediaImporterTest extends TestCase
{
    /**
     * @var WikiImporter
     */
    private $wikiImporter;

    public function setUp(): void
    {
        $this->wikiImporter = new WikiImporter();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->wikiImporter);

        parent::tearDown();
    }

    public function testGetGameModifiedFieldsNoChange()
    {
        $feedItemGame = FeedItemGameFactory::makeSimpleForNoChange();
        $gameReleaseDates = GameReleaseDateFactory::makeSimpleCollectionForNoChange();

        $game = new Game();
        $game->developer = 'Developer 1';
        $game->publisher = 'Publisher 1';

        $expectedFields = [];

        $fields = $this->wikiImporter->getGameModifiedFields($feedItemGame, $game, $gameReleaseDates);

        $this->assertEquals($expectedFields, $fields);
    }

    public function testGetGameModifiedFieldsWithDifferences()
    {
        $feedItemGame = FeedItemGameFactory::makeFullWithDifferences();
        $gameReleaseDates = GameReleaseDateFactory::makeFullCollectionWithChanges();

        $game = new Game();
        $game->developer = 'ABC Developer';
        $game->publisher = 'ABC Publisher';

        $expectedFields = [
            'item_developers', 'item_publishers',
            'release_date_eu', 'upcoming_date_eu',
            'release_date_us', 'upcoming_date_us',
            'release_date_jp', 'upcoming_date_jp',
        ];

        $fields = $this->wikiImporter->getGameModifiedFields($feedItemGame, $game, $gameReleaseDates);

        $this->assertEquals($expectedFields, $fields);
    }

    public function testGetGameModifiedFieldsWithDifferencesAndIgnoreRules()
    {
        $feedItemGame = FeedItemGameFactory::makeFullWithDifferences();
        $gameReleaseDates = GameReleaseDateFactory::makeFullCollectionWithChanges();
        $gameImportRule = GameImportRuleWikipediaFactory::makeWithAllEnabled();

        $game = new Game();
        $game->developer = 'ABC Developer';
        $game->publisher = 'ABC Publisher';

        $expectedFields = [];

        $fields = $this->wikiImporter->getGameModifiedFields($feedItemGame, $game, $gameReleaseDates, $gameImportRule);

        $this->assertEquals($expectedFields, $fields);
    }
}
