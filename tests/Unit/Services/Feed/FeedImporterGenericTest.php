<?php

namespace Tests\Unit\Services\Feed;

use Tests\TestCase;

use App\Services\Feed\Importer;

class FeedImporterGenericTest extends TestCase
{
    /**
     * @var Importer
     */
    private $feedImporter;

    public function setUp(): void
    {
        $this->feedImporter = new Importer();

        parent::setUp();
    }

    public function tearDown(): void
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
