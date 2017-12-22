<?php

namespace Tests\Unit\Services\Feed;

use Illuminate\Support\Collection;
use Tests\TestCase;
#use Illuminate\Foundation\Testing\DatabaseMigrations;
#use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\Feed\Importer;

class ImporterNLStubFeedTest extends TestCase
{
    /**
     * @var Importer
     */
    private $feedImporter;

    public function setUp()
    {
        $this->feedImporter = new Importer();

        $feedData = [
            'channel' => [
                'item' => [
                    [
                        'pubDate' => 'Wed, 15 Nov 2017 13:30:00 GMT',
                        'title' => 'Some Random Game 1',
                        'description' => [],
                        'link' => 'http://www.worldofswitch.com/test-test-test-1',
                        'guid' => 'http://www.worldofswitch.com/test-test-test-1',
                        'score' => '7'
                    ],
                    [
                        'pubDate' => 'Tue, 14 Nov 2017 14:00:00 GMT',
                        'title' => 'Some Random Game 2',
                        'description' => [],
                        'link' => 'http://www.worldofswitch.com/test-test-test-1',
                        'guid' => 'http://www.worldofswitch.com/test-test-test-2',
                        'score' => '6'
                    ],
                ]
            ]
        ];

        $this->feedImporter->setStubFeedData($feedData);

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->feedImporter);

        parent::tearDown();
    }

    public function testFeedLoaded()
    {
        $this->assertNotEmpty($this->feedImporter->getFeedData());
    }

    public function testFeedKeyChannel()
    {
        $this->assertArrayHasKey('channel', $this->feedImporter->getFeedData());
    }

    public function testFeedKeyItem()
    {
        $feedData = $this->feedImporter->getFeedData();

        $this->assertArrayHasKey('item', $feedData['channel']);
    }

    public function testFeedKeyItemCount()
    {
        $feedData = $this->feedImporter->getFeedData();

        $this->assertCount(2, $feedData['channel']['item']);
    }
}
