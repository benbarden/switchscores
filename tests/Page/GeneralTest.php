<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GeneralTest extends TestCase
{
    public function testHomepage()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function testAbout()
    {
        $response = $this->get('/about');
        $response->assertStatus(200);
    }
}
