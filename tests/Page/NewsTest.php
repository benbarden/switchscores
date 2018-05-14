<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NewsTest extends TestCase
{
    public function testHomepage()
    {
        $response = $this->get('/news');
        $response->assertStatus(200);
    }

    public function testPost()
    {
        $response = $this->get('/news/20180317/stats-milestones-500-games-and-3000-review-scores');
        $response->assertStatus(200);
    }

    public function testNotFound()
    {
        $response = $this->get('/news/20180317/fake-post');
        $response->assertStatus(404);

        $response = $this->get('/news/what/is-going-on');
        $response->assertStatus(404);
    }
}
