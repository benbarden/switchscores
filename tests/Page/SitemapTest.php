<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Enums\HttpStatus;

class SitemapTest extends TestCase
{
    public function doPageTest($url, $status = HttpStatus::STATUS_OK)
    {
        $response = $this->get($url);
        $response->assertStatus($status->value);
    }

    public function testSitemap()
    {
        $this->doPageTest('/sitemap', HttpStatus::REDIR_PERM);
    }

    public function testSitemapSite()
    {
        $this->doPageTest('/sitemap/site', HttpStatus::REDIR_PERM);
    }

    public function testSitemapTags()
    {
        $this->doPageTest('/sitemap/tags', HttpStatus::REDIR_PERM);
    }

    public function testSitemapGames()
    {
        $this->doPageTest('/sitemap/games', HttpStatus::REDIR_PERM);
    }

}
