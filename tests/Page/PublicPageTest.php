<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PublicPageTest extends TestCase
{
    const HTTP_OK = 200;
    const HTTP_REDIR_PERM = 301;
    const HTTP_REDIR_TEMP = 302;

    public function doPageTest($url, $status = self::HTTP_OK)
    {
        $response = $this->get($url);
        $response->assertStatus($status);
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

        $this->doPageTest("/lists", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/games/recent", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/games/upcoming", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/games/on-sale");
        //$this->doPageTest("/games/on-sale/archive");
        $this->doPageTest("/lists/recently-ranked");
        //$this->doPageTest("/lists/recently-reviewed-still-unranked");

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

        $this->doPageTest("/games/1", self::HTTP_REDIR_PERM);

        $this->doPageTest('/games/1/the-legend-of-zelda-breath-of-the-wild');

        $this->doPageTest("/sitemap", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/sitemap/site", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/sitemap/games", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/sitemap/calendar", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/sitemap/top-rated", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/sitemap/reviews", self::HTTP_REDIR_TEMP);
        $this->doPageTest("/sitemap/tags", self::HTTP_REDIR_TEMP);
        //$this->doPageTest("/sitemap/news");
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