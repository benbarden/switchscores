<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PublicPageTest extends TestCase
{
    public function doPageTest($url)
    {
        $response = $this->get($url);
        $response->assertStatus(200);
    }

    public function doPageNotFoundTest($url)
    {
        $response = $this->get($url);
        $response->assertStatus(404);
    }

    public function testPublicPages()
    {
        $this->doPageTest("/");

        $this->doPageTest("/about");
        $this->doPageTest("/about/changelog");

        $this->doPageTest("/privacy");

        $this->doPageTest("/community");

        $this->doPageTest("/partners");
        $this->doPageTest("/partners/review-sites");
        $this->doPageTest("/partners/games-companies");
        $this->doPageTest("/partners/games-company/hamster-corporation");
        $this->doPageTest("/reviews/site/nintendo-life");

        $this->doPageTest("/help");
        $this->doPageTest("/help/low-quality-filter");

        $this->doPageTest("/lists");
        $this->doPageTest("/games/recent");
        $this->doPageTest("/games/upcoming");
        $this->doPageTest("/games/on-sale");
        $this->doPageTest("/games/on-sale/archive");
        $this->doPageTest("/lists/recently-ranked");
        $this->doPageTest("/lists/recently-reviewed-still-unranked");

        $this->doPageTest("/news");
        $this->doPageTest("/news/category/editorial");
        $this->doPageTest("/news/20180317/stats-milestones-500-games-and-3000-review-scores");

        $this->doPageTest("/reviews");
        $this->doPageTest("/reviews/2017");
        $this->doPageTest("/reviews/2018");
        $this->doPageTest("/reviews/2019");
        $this->doPageTest("/reviews/2020");
        $this->doPageTest("/reviews/2021");

        $this->doPageTest("/top-rated");
        $this->doPageTest("/top-rated/all-time");
        $this->doPageTest("/top-rated/by-year/2017");
        $this->doPageTest("/top-rated/by-year/2018");
        $this->doPageTest("/top-rated/by-year/2019");
        $this->doPageTest("/top-rated/by-year/2020");
        $this->doPageTest("/top-rated/by-year/2021");

        $this->doPageTest("/games");
        $this->doPageTest("/games/search");

        $this->doPageTest("/games/by-category");
        $this->doPageTest("/games/by-category/adventure");
        $this->doPageTest("/games/by-series/pokemon");
        $this->doPageTest("/games/by-tag");
        $this->doPageTest("/games/by-tag/board-game");
        $this->doPageTest("/games/by-date");
        $this->doPageTest("/games/by-date/2020-01");

        $response = $this->get('/games/1');
        $response->assertStatus(301);

        $this->doPageTest('/games/1/the-legend-of-zelda-breath-of-the-wild');

        $this->doPageTest("/sitemap");
        $this->doPageTest("/sitemap/site");
        $this->doPageTest("/sitemap/games");
        $this->doPageTest("/sitemap/calendar");
        $this->doPageTest("/sitemap/top-rated");
        $this->doPageTest("/sitemap/reviews");
        $this->doPageTest("/sitemap/tags");
        $this->doPageTest("/sitemap/news");
    }

    public function testPageNotFound()
    {
        $this->doPageNotFoundTest("/top-rated/by-year");
        $this->doPageNotFoundTest("/top-rated/by-year/2016");
        $this->doPageNotFoundTest("/top-rated/by-year/2036");
        $this->doPageNotFoundTest("/reviews/site/not-really-a-site");
        $this->doPageNotFoundTest("/news/20180317/fake-post");
    }
}