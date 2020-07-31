<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MainPageTest extends TestCase
{
    public function testMainNavLinks()
    {
        $navLinks = [
            '/',
            '/reviews',
            '/partners',
            '/news',
            '/about',
            '/privacy',
        ];

        foreach ($navLinks as $navLink) {
            $response = $this->get($navLink);
            $response->assertStatus(200);
        }
    }

    public function testGamesLandingPages()
    {
        $navLinks = [
            '/games',
            '/games/recent',
            '/games/upcoming',
            '/games/on-sale',
            '/games/by-title',
            '/games/by-date',
            '/games/by-category',
            '/games/by-tag',
            '/games/by-series',
        ];

        foreach ($navLinks as $navLink) {
            $response = $this->get($navLink);
            $response->assertStatus(200);
        }
    }

    public function testTopRatedPages()
    {
        $navLinks = [
            '/top-rated',
            '/top-rated/all-time',
            '/top-rated/by-year/2017',
            '/top-rated/by-year/2018',
            '/top-rated/by-year/2019',
            '/top-rated/by-year/2020',
            '/top-rated/multiplayer',
            '/reviews/site/nintendo-life',
            '/news/20180317/stats-milestones-500-games-and-3000-review-scores',
        ];

        foreach ($navLinks as $navLink) {
            $response = $this->get($navLink);
            $response->assertStatus(200);
        }
    }

    public function testNotFoundPages()
    {
        $navLinks = [
            '/top-rated/by-year',
            '/top-rated/by-year/2016',
            '/top-rated/by-year/2021',
            '/reviews/site/not-really-a-site',
            '/news/20180317/fake-post',
            '/news/what/is-going-on',
        ];

        foreach ($navLinks as $navLink) {
            $response = $this->get($navLink);
            $response->assertStatus(404);
        }
    }
}