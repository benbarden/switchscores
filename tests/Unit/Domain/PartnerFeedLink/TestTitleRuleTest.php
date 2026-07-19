<?php

namespace Tests\Unit\Domain\PartnerFeedLink;

use App\Domain\PartnerFeedLink\TestTitleRule;

use Tests\TestCase;

/**
 * Covers the parts of the rule tester that do not need a database: pattern validation, the
 * warnings for rules that look right but cannot match, and the rule suggestion heuristic.
 *
 * Cases where a rule matches and a game is then looked up are deliberately not covered here -
 * those need fixtures.
 */
class TestTitleRuleTest extends TestCase
{
    /**
     * @var TestTitleRule
     */
    private $testTitleRule;

    public function setUp(): void
    {
        parent::setUp();

        $this->testTitleRule = new TestTitleRule();
    }

    public function tearDown(): void
    {
        unset($this->testTitleRule);

        parent::tearDown();
    }

    public function testAValidPatternIsAccepted()
    {
        $result = $this->testTitleRule->setRule('(.*) Review', 1)->validatePattern();

        $this->assertTrue($result['valid']);
        $this->assertNull($result['error']);
        $this->assertEmpty($result['warnings']);
    }

    public function testThePreparedPatternIsAnchored()
    {
        $result = $this->testTitleRule->setRule('(.*) Review', 1)->validatePattern();

        $this->assertEquals('/^(.*) Review$/', $result['prepared']);
    }

    public function testAnUncompilablePatternIsRejected()
    {
        $result = $this->testTitleRule->setRule('(.* Review', 1)->validatePattern();

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['error']);
    }

    public function testAMissingPatternIsRejected()
    {
        $result = $this->testTitleRule->setRule('', 1)->validatePattern();

        $this->assertFalse($result['valid']);
    }

    public function testAMissingIndexIsRejected()
    {
        $result = $this->testTitleRule->setRule('(.*) Review', null)->validatePattern();

        $this->assertFalse($result['valid']);
    }

    /**
     * Rules run without the /u modifier, so a multibyte character in a character class matches
     * a single byte and can never fire. [-–] looks correct and matches nothing.
     */
    public function testAMultibyteCharacterInACharacterClassIsWarnedAbout()
    {
        $result = $this->testTitleRule->setRule('(.*) [-–] Review', 1)->validatePattern();

        $this->assertTrue($result['valid']);
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('multibyte', $result['warnings'][0]);
    }

    public function testAnAsciiCharacterClassIsNotWarnedAbout()
    {
        $result = $this->testTitleRule->setRule('(.*) [-:] Review', 1)->validatePattern();

        $this->assertEmpty($result['warnings']);
    }

    public function testAPatternWithNoCaptureGroupIsWarnedAbout()
    {
        $result = $this->testTitleRule->setRule('.* Review', 1)->validatePattern();

        $this->assertNotEmpty($result['warnings']);
        $this->assertStringContainsString('capture group', $result['warnings'][0]);
    }

    public function testATitleThatDoesNotMatchIsReportedAsSuch()
    {
        $result = $this->testTitleRule
            ->setRule('(.*) \(Nintendo Switch\)', 1)
            ->testTitle('Some Game (PlayStation)');

        $this->assertEquals(TestTitleRule::STATUS_RULE_NO_MATCH, $result['status']);
        $this->assertNull($result['parsed_title']);
    }

    public function testSuggestARuleFromACommonSuffix()
    {
        $titles = [
            'Gato Roboto Review (Nintendo Switch)',
            'Vampire Crawlers Review (Nintendo Switch)',
            'Spica Adventure Review (Nintendo Switch)',
        ];

        $suggestion = $this->testTitleRule->suggestRule($titles);

        $this->assertEquals(1, $suggestion['index']);

        // Assert on behaviour rather than the exact string: what matters is that the suggested
        // rule parses the sample titles back to the game name.
        $result = $this->testTitleRule
            ->setRule($suggestion['pattern'], $suggestion['index'])
            ->testTitle('Gato Roboto Review (Nintendo Switch)');

        $this->assertEquals('Gato Roboto', $result['parsed_title']);
    }

    public function testSuggestARuleFromACommonPrefixAndSuffix()
    {
        $titles = [
            'Review: Donkey Kong (Switch)',
            'Review: Mario Kart (Switch)',
        ];

        $suggestion = $this->testTitleRule->suggestRule($titles);

        $result = $this->testTitleRule
            ->setRule($suggestion['pattern'], $suggestion['index'])
            ->testTitle('Review: Donkey Kong (Switch)');

        $this->assertEquals('Donkey Kong', $result['parsed_title']);
    }

    public function testNoRuleIsSuggestedFromASingleTitle()
    {
        $suggestion = $this->testTitleRule->suggestRule(['Gato Roboto Review (Nintendo Switch)']);

        $this->assertNull($suggestion['pattern']);
    }

    public function testNoRuleIsSuggestedWhenTitlesShareNothing()
    {
        $suggestion = $this->testTitleRule->suggestRule(['Alpha', 'Beta']);

        $this->assertNull($suggestion['pattern']);
    }
}
