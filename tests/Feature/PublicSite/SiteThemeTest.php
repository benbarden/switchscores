<?php

namespace Tests\Feature\PublicSite;

use Tests\TestCase;

/**
 * The shared public theme, checked through a page that uses it.
 */
class SiteThemeTest extends TestCase
{
    public function test_the_banner_reserves_its_space_so_the_page_does_not_jump(): void
    {
        $response = $this->get('/login');

        $response->assertSee('width="1170" height="200"', false);
    }

    public function test_public_pages_do_not_load_the_twitter_script(): void
    {
        $response = $this->get('/login');

        $response->assertDontSee('platform.twitter.com/widgets.js', false);
        $response->assertDontSee('twitter-share-button', false);
    }
}
