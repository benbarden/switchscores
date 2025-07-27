<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Enums\HttpStatus;

class PublicPageTest extends TestCase
{
    public function doPageTest($url, $status = HttpStatus::STATUS_OK)
    {
        $response = $this->get($url);
        $response->assertStatus($status->value);
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

        $this->doPageTest("/lists", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/recent", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/upcoming", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/on-sale");
        //$this->doPageTest("/games/on-sale/archive");
        $this->doPageTest("/lists/recently-ranked");
        //$this->doPageTest("/lists/recently-reviewed-still-unranked");

        $this->doPageTest("/news");
        $this->doPageTest("/news/category/editorial");
        $this->doPageTest("/news/20180317/stats-milestones-500-games-and-3000-review-scores");

        $this->doPageTest("/reviews");
        $this->doPageTest("/reviews/2017", HttpStatus::REDIR_TEMP);
        $this->doPageTest("/reviews/2018", HttpStatus::REDIR_TEMP);
        $this->doPageTest("/reviews/2019", HttpStatus::REDIR_TEMP);
        $this->doPageTest("/reviews/2020", HttpStatus::REDIR_TEMP);
        $this->doPageTest("/reviews/2021", HttpStatus::REDIR_TEMP);

        $this->doPageTest("/top-rated", HttpStatus::REDIR_PERM);
        $this->doPageTest("/top-rated/all-time", HttpStatus::REDIR_PERM);
        $this->doPageTest("/top-rated/by-year/2017", HttpStatus::REDIR_PERM);
        $this->doPageTest("/top-rated/by-year/2018", HttpStatus::REDIR_PERM);
        $this->doPageTest("/top-rated/by-year/2019", HttpStatus::REDIR_PERM);
        $this->doPageTest("/top-rated/by-year/2020", HttpStatus::REDIR_PERM);
        $this->doPageTest("/top-rated/by-year/2021", HttpStatus::REDIR_PERM);

        $this->doPageTest("/sitemap", HttpStatus::REDIR_PERM);
        $this->doPageTest("/sitemap/site", HttpStatus::REDIR_PERM);
        $this->doPageTest("/sitemap/games", HttpStatus::REDIR_PERM);
        $this->doPageTest("/sitemap/calendar", HttpStatus::REDIR_PERM);
        $this->doPageTest("/sitemap/top-rated", HttpStatus::REDIR_PERM);
        $this->doPageTest("/sitemap/reviews", HttpStatus::REDIR_PERM);
        $this->doPageTest("/sitemap/tags", HttpStatus::REDIR_PERM);
        //$this->doPageTest("/sitemap/news");

        $this->doPageTest("/top-rated/by-year/2016", HttpStatus::REDIR_PERM);
        $this->doPageTest("/top-rated/by-year/2036", HttpStatus::REDIR_PERM);
    }

    public function testPageNotFound()
    {
        $this->doPageNotFoundTest("/top-rated/by-year");
        $this->doPageNotFoundTest("/reviews/site/not-really-a-site");
        $this->doPageNotFoundTest("/news/20180317/fake-post");
    }
}