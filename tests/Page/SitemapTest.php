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

        $response->assertStatus(200);
    }

    public function testSitemapSite()
    {
        $response = $this->get('/sitemap/site');

        $response->assertStatus(200);
    }

    public function testSitemapGenres()
    {
        $response = $this->get('/sitemap/genres');

        $response->assertStatus(200);
    }

    public function testSitemapTags()
    {
        $response = $this->get('/sitemap/tags');

        $response->assertStatus(200);
    }

    public function testSitemapGames()
    {
        $response = $this->get('/sitemap/games');

        $response->assertStatus(200);
    }

    public function testSitemapNews()
    {
        $response = $this->get('/sitemap/news');

        $response->assertStatus(200);
    }

}
