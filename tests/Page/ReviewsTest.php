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
        $response = $this->get('/top-rated');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/all-time');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/by-year');
        $response->assertStatus(404);

        $response = $this->get('/top-rated/by-year/2016');
        $response->assertStatus(404);

        $response = $this->get('/top-rated/by-year/2017');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/by-year/2018');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/by-year/2019');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/by-year/2020');
        $response->assertStatus(404);
    }

    public function testUnrankedGames()
    {
        $response = $this->get('/reviews/not-ranked/by-count/2');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-count/1');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-count/0');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-count/3');
        $response->assertStatus(404);

        $response = $this->get('/reviews/not-ranked/by-year/2017');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-year/2018');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-year/2019');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-year/2020');
        $response->assertStatus(404);

        $response = $this->get('/reviews/not-ranked/by-list/aca-neogeo');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-list/arcade-archives');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-list/all-others');
        $response->assertStatus(200);

        $response = $this->get('/reviews/not-ranked/by-list/nothing-much-here');
        $response->assertStatus(404);

        $response = $this->get('/reviews/games-needing-reviews');
        $response->assertStatus(302);
    }

    public function testReviewSitePage()
    {
        $response = $this->get('/reviews/site/nintendo-life');
        $response->assertStatus(200);

        $response = $this->get('/reviews/site/not-really-a-site');
        $response->assertStatus(404);
    }
}
