<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ChartsTest extends TestCase
{
    public function testLanding()
    {
        $response = $this->get('/charts');
        $response->assertStatus(200);
    }

    public function testWeeklyCountryCharts()
    {
        $response = $this->get('/charts/eu/2018-05-12');
        $response->assertStatus(200);

        $response = $this->get('/charts/us/2018-05-12');
        $response->assertStatus(200);

        $response = $this->get('/charts/2018-05-12');
        $response->assertStatus(301);

        $response = $this->get('/charts-us/2018-05-12');
        $response->assertStatus(301);

        $response = $this->get('/charts/eu/2018-05-10');
        $response->assertStatus(404);

        $response = $this->get('/charts/us/2018-05-10');
        $response->assertStatus(404);

        $response = $this->get('/charts/jp/2018-05-12');
        $response->assertStatus(404);
    }

    public function testMostAppearances()
    {
        $response = $this->get('/charts/most-appearances');
        $response->assertStatus(200);
    }

    public function testGamesAtPosition()
    {
        $response = $this->get('/charts/games-at-position');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/x');
        $response->assertStatus(404);

        $response = $this->get('/charts/games-at-position/1');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/2');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/3');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/4');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/5');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/6');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/7');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/8');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/9');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/10');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/11');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/12');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/13');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/14');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/15');
        $response->assertStatus(200);

        $response = $this->get('/charts/games-at-position/16');
        $response->assertStatus(404);
    }
}
