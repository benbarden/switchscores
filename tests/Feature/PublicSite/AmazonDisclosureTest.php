<?php

namespace Tests\Feature\PublicSite;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Amazon's terms require this statement wherever the site carries affiliate links.
 */
class AmazonDisclosureTest extends TestCase
{
    use DatabaseTransactions;

    private const STATEMENT = 'As an Amazon Associate I earn from qualifying purchases.';

    public function testPublicFooterHasTheStatement()
    {
        $this->get(route('about.landing'))->assertStatus(200)->assertSee(self::STATEMENT);
    }

    public function testPrivacyPageExplainsTheLinks()
    {
        $this->get(route('privacy'))
            ->assertStatus(200)
            ->assertSee('Affiliate links')
            ->assertSee('Some links on this site are Amazon affiliate links.');
    }

    public function testMemberFooterHasTheStatement()
    {
        $member = new User([
            'display_name' => 'Disclosure Test',
            'email' => 'xx.disclosure.test.'.uniqid().'@switchscores.com',
        ]);

        $this->actingAs($member)->get('/members/wishlist')->assertStatus(200)->assertSee(self::STATEMENT);
    }
}
