<?php

namespace Tests\Unit\Services\Feed;

use App\FeedItemReview;
use App\Partner;
use Illuminate\Support\Collection;
use Tests\TestCase;

use App\Services\Feed\Importer;

class FeedImporterGenericTest extends TestCase
{
    /**
     * @var Importer
     */
    private $feedImporter;

    public function setUp()
    {
        $this->feedImporter = new Importer();

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->feedImporter);

        parent::tearDown();
    }

    public function testConvertResponseToArray()
    {
        $body = '<p>hello</p>';
        $array = $this->feedImporter->convertResponseToJson($body);
        $this->assertNotEmpty($array);
    }
}
