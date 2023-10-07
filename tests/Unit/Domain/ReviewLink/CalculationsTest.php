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
}
