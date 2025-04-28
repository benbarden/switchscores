<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SitemapTest extends TestCase
{
    public function testSitemap()
    {
        $response = $this->get('/sitemap');

        $response->assertStatus(302);
    }

    public function testSitemapSite()
    {
        $response = $this->get('/sitemap/site');

        $response->assertStatus(302);
    }

    public function testSitemapTags()
    {
        $response = $this->get('/sitemap/tags');

        $response->assertStatus(302);
    }

    public function testSitemapGames()
    {
        $response = $this->get('/sitemap/games');

        $response->assertStatus(302);
    }

}
