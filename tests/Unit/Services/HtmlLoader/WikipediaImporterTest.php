<?php

namespace Tests\Unit\Services\HtmlLoader;

use App\Services\HtmlLoader\Wikipedia\Importer as WikiImporter;
use Illuminate\Support\Collection;
use Tests\TestCase;

use App\Game;
use App\GameReleaseDate;
use App\FeedItemGame;

class WikipediaImporterTest extends TestCase
{
    /**
     * @var WikiImporter
     */
    private $wikiImporter;

    public function setUp()
    {
        $this->wikiImporter = new WikiImporter();

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->wikiImporter);

        parent::tearDown();
    }

    public function testGetGameModifiedFieldsNoChange()
    {
        //$newFeedItem = factory(FeedItemGame::class)->make([
        //    'game_id' => 1
        //]);

        $newFeedItem = new FeedItemGame();
        $newFeedItem->item_developers = 'Developer 1';
        $newFeedItem->item_publishers = 'Publisher 1';
        $newFeedItem->release_date_eu = '2017-03-03';
        $newFeedItem->upcoming_date_eu = '2017-03-03';
        $newFeedItem->is_released_eu = '1';

        $game = new Game();
        $game->developer = 'Developer 1';
        $game->publisher = 'Publisher 1';

        $gameReleaseDate = new GameReleaseDate();
        $gameReleaseDate->region = 'eu';
        $gameReleaseDate->release_date = '2017-03-03';
        $gameReleaseDate->upcoming_date = '2017-03-03';
        $gameReleaseDate->is_released = '1';

        $expectedFields = [];

        $gameReleaseDates = new Collection();
        $gameReleaseDates->push($gameReleaseDate);

        $fields = $this->wikiImporter->getGameModifiedFields($newFeedItem, $game, $gameReleaseDates);

        $this->assertEquals($expectedFields, $fields);
    }

}
