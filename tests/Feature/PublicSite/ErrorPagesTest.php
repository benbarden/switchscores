<?php

namespace Tests\Feature\PublicSite;

use Tests\TestCase;

/**
 * The public 404 and 410 pages: a game search box and links to the main sections.
 */
class ErrorPagesTest extends TestCase
{
    public function testNotFoundOffersSearchAndLinks()
    {
        $this->get('/no-such-public-page')
            ->assertStatus(404)
            ->assertSee('Page not found')
            ->assertSee('action="'.route('games.search').'"', false)
            ->assertSee('name="search_keywords"', false)
            ->assertSee(route('console.topRated.landing', ['console' => 'switch-2']))
            ->assertSee(route('contact.show'))
            ->assertDontSee('twitter.com/switchscores');
    }

    public function testRemovedOffersSearchAndLinks()
    {
        $this->view('errors.410')
            ->assertSee('This page has been removed')
            ->assertSee('name="search_keywords"', false)
            ->assertSee(route('browse.byCategory.landing'));
    }
}
