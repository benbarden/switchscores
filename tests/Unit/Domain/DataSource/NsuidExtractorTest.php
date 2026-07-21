<?php

namespace Tests\Unit\Domain\DataSource;

use App\Domain\DataSource\NintendoCoUk\NsuidExtractor;
use Tests\TestCase;

/**
 * The fetch command and the parser both derive NSUIDs through this class, so that the
 * set of ids fetched cannot drift from the set looked up. A drift would present as
 * "the price API had no answer for this record" when the price had in fact been
 * fetched and stored perfectly well - a confusing failure to diagnose from the counts.
 */
class NsuidExtractorTest extends TestCase
{
    private function extractor(): NsuidExtractor
    {
        return new NsuidExtractor();
    }

    public function testExtractsNsuidsFromAnArray()
    {
        $nsuids = $this->extractor()->extract([
            'nsuid_txt' => ['70010000106685', '70050000065152'],
        ]);

        $this->assertEquals(['70010000106685', '70050000065152'], $nsuids);
    }

    public function testReturnsEmptyWhenTheFieldIsAbsent()
    {
        $this->assertEquals([], $this->extractor()->extract(['title' => 'Some game']));
    }

    public function testReturnsEmptyForNullInput()
    {
        $this->assertEquals([], $this->extractor()->extract(null));
    }

    /**
     * Array-vs-scalar is exactly the shape difference that broke the review-feed
     * importer (object vs array parse mode), so a lone NSUID arriving bare is handled
     * rather than assumed away.
     */
    public function testHandlesASingleNsuidArrivingAsAString()
    {
        $this->assertEquals(['70010000106685'], $this->extractor()->extract([
            'nsuid_txt' => '70010000106685',
        ]));
    }

    public function testTrimsAndDropsEmptyEntries()
    {
        $nsuids = $this->extractor()->extract([
            'nsuid_txt' => [' 70010000106685 ', '', '   ', '70050000065152'],
        ]);

        $this->assertEquals(['70010000106685', '70050000065152'], $nsuids);
    }

    public function testDeduplicates()
    {
        $nsuids = $this->extractor()->extract([
            'nsuid_txt' => ['70010000106685', '70010000106685'],
        ]);

        $this->assertEquals(['70010000106685'], $nsuids);
    }

    public function testExtractsFromRawJsonString()
    {
        $json = json_encode(['nsuid_txt' => ['70010000106685']]);

        $this->assertEquals(['70010000106685'], $this->extractor()->extractFromJson($json));
    }

    public function testMalformedJsonYieldsNothingRatherThanThrowing()
    {
        $this->assertEquals([], $this->extractor()->extractFromJson('{not json'));
        $this->assertEquals([], $this->extractor()->extractFromJson(null));
        $this->assertEquals([], $this->extractor()->extractFromJson(''));
    }
}
