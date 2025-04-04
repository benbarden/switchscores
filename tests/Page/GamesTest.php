<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GamesTest extends TestCase
{
    public function testGamesDetailPage()
    {
        $response = $this->get('/games/2');
        $response->assertStatus(301);

        $response = $this->get('/games/2/abc');
        $response->assertStatus(301);

        $response = $this->get('/games/2/1-2-switch');
        $response->assertStatus(200);
    }

    public function testGamesBrowseByDatePage()
    {
        $response = $this->get('/games/by-date/2020-01');
        $response->assertStatus(200);

        $response = $this->get('/games/by-date/2019-12');
        $response->assertStatus(200);

        $response = $this->get('/games/by-date/2018-05');
        $response->assertStatus(200);

        $response = $this->get('/games/by-date/2018-01');
        $response->assertStatus(200);

        $response = $this->get('/games/by-date/2017-03');
        $response->assertStatus(200);

        $response = $this->get('/games/by-date/2017-02');
        $response->assertStatus(404);

        $response = $this->get('/games/by-date/2016-01');
        $response->assertStatus(404);

    }
}
