<?php

namespace Tests\Unit\Services\HtmlLoader;

use App\Factories\Tests\GameImportRuleWikipediaFactory;
use Tests\TestCase;

use App\Game;
use App\Services\HtmlLoader\Wikipedia\Importer as WikiImporter;

use App\Factories\Tests\FeedItemGameFactory;
use App\Factories\Tests\GameFactory;

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
        $game = new Game();
        $game->developer = 'Developer 1';
        $game->publisher = 'Publisher 1';

        $game = GameFactory::makeSimpleGameForNoChange($game);
        $feedItemGame = FeedItemGameFactory::makeSimpleForNoChange();

        $expectedFields = [];

        $fields = $this->wikiImporter->getGameModifiedFields($feedItemGame, $game);

        $this->assertEquals($expectedFields, $fields);
    }

    public function testGetGameModifiedFieldsWithDifferences()
    {
        $game = new Game();
        $game->developer = 'ABC Developer';
        $game->publisher = 'ABC Publisher';

        $game = GameFactory::makeFullCollectionWithChanges($game);
        $feedItemGame = FeedItemGameFactory::makeFullWithDifferences();

        $expectedFields = [
            'item_developers', 'item_publishers',
            'release_date_eu', 'release_date_us', 'release_date_jp',
        ];

        $fields = $this->wikiImporter->getGameModifiedFields($feedItemGame, $game);

        $this->assertEquals($expectedFields, $fields);
    }

    public function testGetGameModifiedFieldsWithDifferencesAndIgnoreRules()
    {
        $game = new Game();
        $game->developer = 'ABC Developer';
        $game->publisher = 'ABC Publisher';

        $game = GameFactory::makeFullCollectionWithChanges($game);
        $feedItemGame = FeedItemGameFactory::makeFullWithDifferences();
        $gameImportRule = GameImportRuleWikipediaFactory::makeWithAllEnabled();

        $expectedFields = [];

        $fields = $this->wikiImporter->getGameModifiedFields($feedItemGame, $game, $gameImportRule);

        $this->assertEquals($expectedFields, $fields);
    }
}
