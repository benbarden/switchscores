<?php

namespace Tests\Unit\Domain\ReviewLink;

use App\Domain\ReviewLink\Calculations;
use Tests\TestCase;

class CalculationsTest extends TestCase
{
    /**
     * @var Calculations
     */
    private $calculations;

    public function setUp(): void
    {
        $this->calculations = new Calculations();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->calculations);

        parent::tearDown();
    }

    public function testNormaliseRatingTenTen()
    {
        $rating = 10;
        $ratingScale = 10;

        $expected = 10;

        $actual = $this->calculations->normaliseRating($rating, $ratingScale);

        $this->assertEquals($expected, $actual);
    }

    public function testNormaliseRatingFiveTen()
    {
        $rating = 5;
        $ratingScale = 10;

        $expected = 5;

        $actual = $this->calculations->normaliseRating($rating, $ratingScale);

        $this->assertEquals($expected, $actual);
    }

    public function testNormaliseRatingFiveFive()
    {
        $rating = 5;
        $ratingScale = 5;

        $expected = 10;

        $actual = $this->calculations->normaliseRating($rating, $ratingScale);

        $this->assertEquals($expected, $actual);
    }

    public function testNormaliseRatingTwoPointFiveFive()
    {
        $rating = 2.5;
        $ratingScale = 5;

        $expected = 5;

        $actual = $this->calculations->normaliseRating($rating, $ratingScale);

        $this->assertEquals($expected, $actual);
    }

    public function testNormaliseRatingHundredHundred()
    {
        $rating = 100;
        $ratingScale = 100;

        $expected = 10;

        $actual = $this->calculations->normaliseRating($rating, $ratingScale);

        $this->assertEquals($expected, $actual);
    }

    public function testNormaliseRatingTenHundred()
    {
        $rating = 10;
        $ratingScale = 100;

        $expected = 1;

        $actual = $this->calculations->normaliseRating($rating, $ratingScale);

        $this->assertEquals($expected, $actual);
    }

    public function testCalculateReviewLinkContributionTenPercent()
    {
        $contribTotal = 100;
        $siteTotal = 1000;
        $expected = 10.0;

        $actual = $this->calculations->contributionPercentage($contribTotal, $siteTotal);

        $this->assertEquals($expected, $actual);
    }

    public function testCalculateReviewLinkContributionHalfPercent()
    {
        $contribTotal = 5;
        $siteTotal = 1000;
        $expected = 0.5;

        $actual = $this->calculations->contributionPercentage($contribTotal, $siteTotal);

        $this->assertEquals($expected, $actual);
    }

    public function testCalculateGameDatabaseCompletionOnePercent()
    {
        $contribTotal = 15;
        $siteTotal = 1500;
        $expected = 1.0;

        $actual = $this->calculations->contributionPercentage($contribTotal, $siteTotal);

        $this->assertEquals($expected, $actual);
    }

    public function testCalculateGameDatabaseCompletionOneHundredPercent()
    {
        $contribTotal = 1500;
        $siteTotal = 1500;
        $expected = 100;

        $actual = $this->calculations->contributionPercentage($contribTotal, $siteTotal);

        $this->assertEquals($expected, $actual);
    }
}
