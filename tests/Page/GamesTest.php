<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GamesTest extends TestCase
{
    public function testGamesPage()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
    }

    public function testGamesDetailPage()
    {
        $response = $this->get('/games/2');
        $response->assertStatus(301);

        $response = $this->get('/games/2/abc');
        $response->assertStatus(301);

        $response = $this->get('/games/2/1-2-switch');
        $response->assertStatus(200);
    }

    public function testGamesReleasedPage()
    {
        $response = $this->get('/games/released');

        $response->assertStatus(200);
    }

    public function testGamesUpcomingPage()
    {
        $response = $this->get('/games/upcoming');

        $response->assertStatus(200);
    }

    public function testGamesUnreleasedPage()
    {
        $response = $this->get('/games/unreleased');

        $response->assertStatus(200);
    }

    public function testGamesReleaseCalendar()
    {
        $response = $this->get('/games/calendar');

        $response->assertStatus(200);
    }

    public function testGamesReleaseCalendarDate()
    {
        $response = $this->get('/games/calendar/2018-05');
        $response->assertStatus(200);

        $response = $this->get('/games/calendar/2018-01');
        $response->assertStatus(200);

        $response = $this->get('/games/calendar/2017-03');
        $response->assertStatus(200);

        $response = $this->get('/games/calendar/2017-02');
        $response->assertStatus(404);

        $response = $this->get('/games/calendar/2016-01');
        $response->assertStatus(404);

    }

    public function testGamesGenres()
    {
        $response = $this->get('/games/genres');
        $response->assertStatus(200);

        $response = $this->get('/games/genres/first-person-shooter');
        $response->assertStatus(200);

        $response = $this->get('/games/genres/platform-games');
        $response->assertStatus(200);

        $response = $this->get('/games/genres/not-a-real-genre');
        $response->assertStatus(404);
    }

    public function testGamesRedirects()
    {
        $response = $this->get('/games/top-rated');
        $response->assertStatus(301);

        $response = $this->get('/games/reviews-needed');
        $response->assertStatus(301);
    }

}
