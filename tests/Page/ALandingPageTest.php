<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ALandingPageTest extends TestCase
{
    public function testHomepage()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function testMainNavLinks()
    {
        $response = $this->get('/about');
        $response->assertStatus(200);

        $response = $this->get('/privacy');
        $response->assertStatus(200);

        $response = $this->get('/reviews');
        $response->assertStatus(200);

        $response = $this->get('/partners');
        $response->assertStatus(200);

        $response = $this->get('/news');
        $response->assertStatus(200);
    }

    public function testGamesLandingPages()
    {
        $response = $this->get('/games');
        $response->assertStatus(200);

        $response = $this->get('/games/recent');
        $response->assertStatus(200);

        $response = $this->get('/games/upcoming');
        $response->assertStatus(200);

        $response = $this->get('/games/on-sale');
        $response->assertStatus(200);

        $response = $this->get('/games/by-title');
        $response->assertStatus(200);

        $response = $this->get('/games/by-date');
        $response->assertStatus(200);

        $response = $this->get('/games/by-category');
        $response->assertStatus(200);

        $response = $this->get('/games/by-tag');
        $response->assertStatus(200);

        $response = $this->get('/games/by-series');
        $response->assertStatus(200);
    }

    public function testTopRatedPages()
    {
        $response = $this->get('/top-rated');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/all-time');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/by-year/2017');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/by-year/2018');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/by-year/2019');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/by-year/2020');
        $response->assertStatus(200);

        $response = $this->get('/top-rated/multiplayer');
        $response->assertStatus(200);

    }
}