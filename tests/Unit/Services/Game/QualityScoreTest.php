<?php

namespace Tests\Unit\Services\Game;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\Game\QualityScore;
use App\Game;

class QualityScoreTest extends TestCase
{
    /**
     * @var QualityScore
     */
    private $serviceQualityScore;

    public function setUp(): void
    {
        parent::setUp();
        $this->serviceQualityScore = new QualityScore;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->serviceQualityScore);
    }

    public function testRuleCount()
    {
        $expectedRuleCount = 16;
        $this->assertEquals($expectedRuleCount, $this->serviceQualityScore->getRuleCount());
    }

    public function testPassingHasCategory()
    {
        $this->serviceQualityScore->setHasCategory(1);
        $gameRules = $this->serviceQualityScore->getGameRules();
        $arrayKey = QualityScore::RULE_HAS_CATEGORY;
        $this->assertArrayHasKey($arrayKey, $gameRules);
        $this->assertEquals(1, $gameRules[$arrayKey]);
    }

    public function testFailingHasCategory()
    {
        $this->serviceQualityScore->setHasCategory(0);

        $gameRules = $this->serviceQualityScore->getGameRules();
        $arrayKey = QualityScore::RULE_HAS_CATEGORY;

        $this->assertArrayHasKey($arrayKey, $gameRules);
        $this->assertEquals(0, $gameRules[$arrayKey]);
    }

    public function testThreePassingRules()
    {
        $this->serviceQualityScore->setHasCategory(1);
        $this->serviceQualityScore->setHasPlayers(1);
        $this->serviceQualityScore->setHasPrice(1);

        $this->assertEquals(3, $this->serviceQualityScore->countPassingRules());
        $this->assertEquals(0, $this->serviceQualityScore->countFailingRules());

        $this->assertEquals(18.75, $this->serviceQualityScore->calculateQualityScore());
    }

    public function testThreeFailedRules()
    {
        $this->serviceQualityScore->setHasCategory(0);
        $this->serviceQualityScore->setHasPlayers(0);
        $this->serviceQualityScore->setHasPrice(0);

        $this->assertEquals(0, $this->serviceQualityScore->countPassingRules());
        $this->assertEquals(3, $this->serviceQualityScore->countFailingRules());

        $this->assertEquals(0.00, $this->serviceQualityScore->calculateQualityScore());
    }

    public function testThreeOfEach()
    {
        $this->serviceQualityScore->setHasCategory(1);
        $this->serviceQualityScore->setHasPlayers(1);
        $this->serviceQualityScore->setHasPrice(1);
        $this->serviceQualityScore->setNoConflictNintendoPlayers(0);
        $this->serviceQualityScore->setNoConflictNintendoEUReleaseDate(0);
        $this->serviceQualityScore->setNoConflictNintendoGenre(0);

        $this->assertEquals(3, $this->serviceQualityScore->countPassingRules());
        $this->assertEquals(3, $this->serviceQualityScore->countFailingRules());

        $this->assertEquals(18.75, $this->serviceQualityScore->calculateQualityScore());
    }

    public function testOnOffFailures()
    {
        $ruleCount = $this->serviceQualityScore->getRuleCount();

        $this->serviceQualityScore->setHasCategory(0);
        $this->serviceQualityScore->setHasCategory(1);

        $this->assertEquals(1, $this->serviceQualityScore->countPassingRules());

        $this->assertEquals((1/$ruleCount)*100, $this->serviceQualityScore->calculateQualityScore());
    }
}
