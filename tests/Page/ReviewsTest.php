<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReviewsTest extends TestCase
{
    public function testHomepage()
    {
        $response = $this->get('/reviews');
        $response->assertStatus(200);
    }

    public function testTopRated()
    {
        $response = $this->get('/reviews/top-rated');
        $response->assertStatus(200);

        $response = $this->get('/reviews/top-rated/all-time');
        $response->assertStatus(200);

        $response = $this->get('/reviews/top-rated/by-year');
        $response->assertStatus(404);

        $response = $this->get('/reviews/top-rated/by-year/2016');
        $response->assertStatus(404);

        $response = $this->get('/reviews/top-rated/by-year/2017');
        $response->assertStatus(200);

        $response = $this->get('/reviews/top-rated/by-year/2018');
        $response->assertStatus(200);

        $response = $this->get('/reviews/top-rated/by-year/2019');
        $response->assertStatus(404);
    }

    public function testGamesNeedingReviews()
    {
        $response = $this->get('/reviews/games-needing-reviews');
        $response->assertStatus(200);
    }

    public function testReviewSitePage()
    {
        $response = $this->get('/reviews/site/nintendo-life');
        $response->assertStatus(200);

        $response = $this->get('/reviews/site/not-really-a-site');
        $response->assertStatus(404);
    }
}
