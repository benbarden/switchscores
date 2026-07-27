<?php

namespace Tests\Unit\Domain\Feed;

use App\Domain\Feed\FeedProbe;
use App\Domain\Feed\FeedProbeResult;
use App\Models\PartnerFeedLink;

use Tests\TestCase;

/**
 * Covers what the probe concludes from a feed body. No network and no database: probeBody()
 * takes the response, and title matching against the games table is deliberately left to the
 * title rule tester.
 *
 * The parse mode cases are the ones that matter. The rule they encode was written down wrongly
 * for a long time - as "an attribute forces Object mode" - and the tests below pin the actual
 * behaviour so it cannot quietly revert to the plausible-sounding version.
 */
class FeedProbeTest extends TestCase
{
    /**
     * @var FeedProbe
     */
    private $probe;

    public function setUp(): void
    {
        parent::setUp();

        $this->probe = new FeedProbe();
    }

    public function tearDown(): void
    {
        unset($this->probe);

        parent::tearDown();
    }

    private function rssFeed($items, $channelTitle = 'Example Reviews', $channelLink = 'https://example.com/')
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<rss version="2.0"><channel>'
            .'<title>'.$channelTitle.'</title>'
            .'<link>'.$channelLink.'</link>'
            .$items
            .'</channel></rss>';
    }

    private function item($title, $extra = '', $link = 'https://example.com/a', $pubDate = 'Sun, 26 Jul 2026 22:59:48 +0000')
    {
        return '<item><title>'.$title.'</title><link>'.$link.'</link>'
            .'<pubDate>'.$pubDate.'</pubDate>'.$extra.'</item>';
    }

    // ---------------------------------------------------------------- item node detection

    public function testDetectsStandardRssItemNode()
    {
        $body = $this->rssFeed($this->item('Alpha Review').$this->item('Beta Review'));

        $result = $this->probe->probeBody($body);

        $this->assertFalse($result->hasLoadError());
        $this->assertEquals(PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM, $result->getDetection('item_node'));
    }

    public function testDetectsAtomEntryNode()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<feed xmlns="http://www.w3.org/2005/Atom">'
            .'<title>Example Reviews</title>'
            .'<link rel="alternate" href="https://example.com/"/>'
            .'<entry><title>Alpha Review</title><link href="https://example.com/a"/>'
            .'<updated>2026-07-26T22:59:48Z</updated></entry>'
            .'<entry><title>Beta Review</title><link href="https://example.com/b"/>'
            .'<updated>2026-07-25T22:59:48Z</updated></entry>'
            .'</feed>';

        $result = $this->probe->probeBody($body);

        $this->assertEquals(PartnerFeedLink::ITEM_NODE_ENTRY, $result->getDetection('item_node'));
        $this->assertEquals('https://example.com/', $result->getDetection('website_url'));
    }

    public function testDetectsRootLevelItemNode()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?><feeditems>'
            .$this->item('Alpha Review').$this->item('Beta Review')
            .'</feeditems>';

        $result = $this->probe->probeBody($body);

        $this->assertEquals(PartnerFeedLink::ITEM_NODE_ITEM, $result->getDetection('item_node'));
    }

    public function testReportsAFeedWithNoRecognisableItems()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?><something><nested>x</nested></something>';

        $result = $this->probe->probeBody($body);

        $this->assertTrue($result->hasLoadError());
        $this->assertStringContainsString('Could not find any items', $result->getLoadError());
    }

    /**
     * Deliberately malformed rather than merely wrong. Well-formed HTML parses happily as
     * XML and is reported as "no items found" instead - which is what most dead partner
     * feeds actually return, so both paths matter.
     */
    public function testReportsAResponseThatIsNotXml()
    {
        $result = $this->probe->probeBody('<html><body><p>Not a feed</body></html>');

        $this->assertTrue($result->hasLoadError());
        $this->assertStringContainsString('not valid XML', $result->getLoadError());
    }

    // ---------------------------------------------------------------- parse mode detection

    /**
     * The rule that was documented wrongly. An attribute alongside a text value does NOT
     * break array mode: json_encode() drops the attribute and keeps the text. Recommending
     * Object mode here would be harmless but wrong, and it is how the wrong rule survived -
     * it was never contradicted by anything.
     */
    public function testAnAttributeOnTheScoreDoesNotForceObjectMode()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<score max="10">7.2</score>')
            .$this->item('Beta Review', '<score max="10">8.0</score>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertEquals(PartnerFeedLink::DATA_TYPE_ARRAY, $result->getDetection('data_type'));
        $this->assertEquals(2, $result->getDetection('items_with_score'));
    }

    /**
     * CDATA is what actually breaks array mode - the value comes back as an empty array.
     */
    public function testCdataInTheTitleForcesObjectMode()
    {
        $body = $this->rssFeed(
            $this->item('<![CDATA[Alpha Review]]>')
            .$this->item('<![CDATA[Beta Review]]>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertEquals(PartnerFeedLink::DATA_TYPE_OBJECT, $result->getDetection('data_type'));
        $this->assertStringContainsString('loses title', $result->getEvidence('data_type'));
    }

    /**
     * The Nintendo Life case, and the reason detection compares fields rather than searching
     * the body for CDATA. That feed contains plenty of CDATA, all of it in elements the
     * importer never reads, and it runs correctly on array mode. A body-wide CDATA check
     * would have moved a working feed onto the other parse mode for no reason.
     */
    public function testCdataInAnUnusedElementDoesNotForceObjectMode()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<description><![CDATA[<p>Long review body</p>]]></description>')
            .$this->item('Beta Review', '<description><![CDATA[<p>Another one</p>]]></description>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertEquals(PartnerFeedLink::DATA_TYPE_ARRAY, $result->getDetection('data_type'));
    }

    public function testCdataInTheScoreForcesObjectMode()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<score><![CDATA[7.2]]></score>')
            .$this->item('Beta Review', '<score><![CDATA[8.0]]></score>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertEquals(PartnerFeedLink::DATA_TYPE_OBJECT, $result->getDetection('data_type'));
        $this->assertStringContainsString('loses score', $result->getEvidence('data_type'));
    }

    /**
     * The loader implements different item nodes in each parse mode, and an unsupported pair
     * returns zero items instead of raising anything. The probe has to pick the mode that can
     * actually walk the node.
     */
    public function testPostItemsForceObjectModeBecauseArrayModeCannotWalkThem()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?><blog>'
            .'<post><title>Alpha Review</title><link>https://example.com/a</link>'
            .'<pubDate>Sun, 26 Jul 2026 22:59:48 +0000</pubDate></post>'
            .'<post><title>Beta Review</title><link>https://example.com/b</link>'
            .'<pubDate>Sat, 25 Jul 2026 22:59:48 +0000</pubDate></post>'
            .'</blog>';

        $result = $this->probe->probeBody($body);

        $this->assertEquals(PartnerFeedLink::ITEM_NODE_POST, $result->getDetection('item_node'));
        $this->assertEquals(PartnerFeedLink::DATA_TYPE_OBJECT, $result->getDetection('data_type'));
        $this->assertStringContainsString('Only Object mode', $result->getEvidence('data_type'));
    }

    public function testAtomForcesArrayModeBecauseObjectModeCannotWalkEntries()
    {
        $body = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<feed xmlns="http://www.w3.org/2005/Atom"><title>Example</title>'
            .'<entry><title>Alpha Review</title><link href="https://example.com/a"/>'
            .'<updated>2026-07-26T22:59:48Z</updated></entry>'
            .'</feed>';

        $result = $this->probe->probeBody($body);

        $this->assertEquals(PartnerFeedLink::DATA_TYPE_ARRAY, $result->getDetection('data_type'));
        $this->assertStringContainsString('Only Array mode', $result->getEvidence('data_type'));
    }

    // ---------------------------------------------------------------- scores

    public function testReadsTheRatingScaleFromTheMaxAttribute()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<score max="10">7.2</score>')
            .$this->item('Beta Review', '<score max="10">8.0</score>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertEquals('10', $result->getDetection('rating_scale'));
    }

    public function testWarnsWhenItemsDisagreeOnTheRatingScale()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<score max="10">7.2</score>')
            .$this->item('Beta Review', '<score max="5">4.0</score>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertStringContainsString('disagree on the rating scale', $this->warningText($result));
    }

    /**
     * item_rating is a decimal(4, 1), so a second decimal place is lost silently on import.
     */
    public function testWarnsWhenScoresAreMorePreciseThanTheColumnStores()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<score max="10">7.25</score>')
            .$this->item('Beta Review', '<score max="10">8.00</score>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertStringContainsString('rounded', $this->warningText($result));
    }

    public function testATrailingZeroIsNotTreatedAsExtraPrecision()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<score max="10">7.20</score>')
            .$this->item('Beta Review', '<score max="10">8.00</score>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertStringNotContainsString('rounded', $this->warningText($result));
    }

    public function testWarnsWhenOnlySomeItemsCarryAScore()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<score max="10">7.2</score>')
            .$this->item('Beta Review')
        );

        $result = $this->probe->probeBody($body);

        $this->assertEquals(1, $result->getDetection('items_with_score'));
        $this->assertStringContainsString('Only 1 of 2 items carry a score', $this->warningText($result));
    }

    /**
     * Casualvania as it was on 2026-07-06: the score was in the feed, but as prose inside the
     * description where the importer cannot reach it. Worth naming precisely, because the
     * useful message to a partner is "expose the number you already have", not "add scores".
     */
    public function testReportsAScoreThatIsPresentAsTextButNotAsAnElement()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review', '<description>A fine game. Rating: 8.0</description>')
            .$this->item('Beta Review', '<description>Less fine. Rating: 5.5</description>')
        );

        $result = $this->probe->probeBody($body);

        $this->assertEquals(0, $result->getDetection('items_with_score'));

        $warnings = $this->warningText($result);
        $this->assertStringContainsString('No score found', $warnings);
        $this->assertStringContainsString('Rating: 8.0', $warnings);
    }

    public function testWarnsWhenNoScoreIsPresentAnywhere()
    {
        $body = $this->rssFeed($this->item('Alpha Review').$this->item('Beta Review'));

        $result = $this->probe->probeBody($body);

        $this->assertEquals(0, $result->getDetection('items_with_score'));
        $this->assertStringContainsString('No score found', $this->warningText($result));
    }

    // ---------------------------------------------------------------- site details + titles

    public function testSuggestsATitleRuleFromTheCommonWrapper()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review (Nintendo Switch)')
            .$this->item('Bravo Review (Nintendo Switch)')
            .$this->item('Charlie Review (Nintendo Switch)')
        );

        $result = $this->probe->probeBody($body);

        $pattern = $result->getDetection('title_match_rule_pattern');

        $this->assertNotNull($pattern);
        $this->assertEquals(1, $result->getDetection('title_match_rule_index'));
        $this->assertEquals(1, preg_match('/^'.$pattern.'$/', 'Delta Review (Nintendo Switch)', $matches));
        $this->assertEquals('Delta', $matches[1]);
    }

    /**
     * A known limit of the suggestion heuristic, pinned here so it is not mistaken for a
     * detection bug later. The rule is built from what every sampled title shares, so if the
     * game names happen to share a trailing character - Alpha, Beta and Gamma all end in "a" -
     * that character is absorbed into the fixed part and the capture loses it.
     *
     * It self-corrects with a realistic sample: the 37 real Casualvania titles produce a
     * clean rule. The suggestion is a starting point for the title rule tester, not an answer,
     * and the probe reports the match count so a poor suggestion is visible rather than
     * silently accepted.
     */
    public function testTheSuggestionAbsorbsACharacterSharedByEveryTitle()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review (Nintendo Switch)')
            .$this->item('Beta Review (Nintendo Switch)')
            .$this->item('Gamma Review (Nintendo Switch)')
        );

        $result = $this->probe->probeBody($body);

        $pattern = $result->getDetection('title_match_rule_pattern');

        preg_match('/^'.$pattern.'$/', 'Delta Review (Nintendo Switch)', $matches);

        $this->assertEquals('Delt', $matches[1]);
    }

    public function testNotesWhenTitlesShareNoWrapperToBuildARuleFrom()
    {
        $body = $this->rssFeed(
            $this->item('Alpha').$this->item('Beta').$this->item('Gamma')
        );

        $result = $this->probe->probeBody($body);

        $this->assertNull($result->getDetection('title_match_rule_pattern'));
        $this->assertStringContainsString('no consistent wrapper', $this->warningText($result));
    }

    public function testTakesTheSiteNameAndUrlFromTheChannel()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review').$this->item('Beta Review'),
            'Examplesite',
            'https://example.com/'
        );

        $result = $this->probe->probeBody($body);

        $this->assertEquals('Examplesite', $result->getDetection('name'));
        $this->assertEquals('examplesite', $result->getDetection('link_title'));
        $this->assertEquals('https://example.com/', $result->getDetection('website_url'));
    }

    /**
     * A channel title is the name of a feed, which is often not the name of the site. Worth
     * flagging rather than silently accepting, because link_title ends up in public URLs.
     */
    public function testFlagsAChannelTitleThatLooksLikeAFeedNameRatherThanASiteName()
    {
        $body = $this->rssFeed(
            $this->item('Alpha Review').$this->item('Beta Review'),
            'Examplesite - Nintendo Switch Reviews'
        );

        $result = $this->probe->probeBody($body);

        $this->assertStringContainsString('looks like the name of the feed', $this->warningText($result));
    }

    // ---------------------------------------------------------------- real feed

    /**
     * The real Casualvania feed, captured 2026-07-27 after the partner added the score
     * element we asked for. Pins the whole result against a genuine payload rather than a
     * shape invented to suit the code.
     */
    public function testProbesTheRealCasualvaniaFeed()
    {
        $body = file_get_contents(__DIR__.'/fixtures/casualvania-reviews.xml');

        $result = $this->probe->probeBody($body, 'https://casualvania.com/feed/casualvania-reviews-switch/');

        $this->assertFalse($result->hasLoadError());
        $this->assertFalse($result->hasErrors());

        $this->assertEquals(PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM, $result->getDetection('item_node'));
        $this->assertEquals(PartnerFeedLink::DATA_TYPE_ARRAY, $result->getDetection('data_type'));
        $this->assertEquals('10', $result->getDetection('rating_scale'));
        $this->assertEquals('https://casualvania.com/', $result->getDetection('website_url'));

        $this->assertEquals(37, $result->getItemCount());
        $this->assertEquals(37, $result->getDetection('items_with_score'));

        $pattern = $result->getDetection('title_match_rule_pattern');
        $this->assertEquals(1, preg_match('/^'.$pattern.'$/', 'Earth Atlantis Review (Nintendo Switch)', $matches));
        $this->assertEquals('Earth Atlantis', $matches[1]);
    }

    private function warningText(FeedProbeResult $result)
    {
        $messages = array_map(function ($warning) {
            return $warning['message'];
        }, $result->getWarnings());

        return implode(' || ', $messages);
    }
}
