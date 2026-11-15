<?php

namespace Tests\Feature\PublicSite;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The banner linking to the buyer's guide: on every public page and the members home, never on
 * the guide's own pages, and gone after banner_until.
 */
class BuyersGuideBannerTest extends TestCase
{
    use DatabaseTransactions;

    private const BANNER = 'See our buyer\'s guide';

    protected function setUp(): void
    {
        parent::setUp();
        config(['buyers-guide.banner_until' => '2031-12-25']);
        $this->travelTo(\Carbon\Carbon::parse('2031-12-01 12:00:00'));
    }

    public function testShownOnPublicPages()
    {
        foreach ([route('welcome'), route('about.landing'), route('reviews.landing')] as $url) {
            $this->get($url)->assertStatus(200)
                ->assertSee(self::BANNER, false)
                ->assertSee('href="'.route('buyersGuide.landing').'" class="alert-link"', false);
        }
    }

    public function testShownOnTheMembersHome()
    {
        $member = new User([
            'display_name' => 'Banner Test',
            'email' => 'xx.banner.test.'.uniqid().'@switchscores.com',
        ]);
        $member->save();

        // The survey banner steps aside while the guide banner shows, and comes back after
        $this->actingAs($member)->get(route('members.index'))->assertStatus(200)
            ->assertSee(self::BANNER, false)
            ->assertDontSee('Take our 2-minute survey');

        config(['buyers-guide.banner_until' => null]);
        $this->actingAs($member)->get(route('members.index'))->assertStatus(200)
            ->assertDontSee(self::BANNER, false)
            ->assertSee('Take our 2-minute survey');
    }

    public function testHomepageSurveyStepsAsideWhileTheBannerShows()
    {
        $this->get(route('welcome'))->assertStatus(200)
            ->assertSee(self::BANNER, false)
            ->assertDontSee('Take our 2-minute survey');

        config(['buyers-guide.banner_until' => null]);
        $this->get(route('welcome'))->assertStatus(200)
            ->assertDontSee(self::BANNER, false)
            ->assertSee('Take our 2-minute survey');
    }

    public function testNotShownOnTheGuidePages()
    {
        $this->get(route('buyersGuide.landing'))->assertStatus(200)->assertDontSee(self::BANNER, false);
        $this->get(route('buyersGuide.region', ['region' => 'us']))->assertStatus(200)->assertDontSee(self::BANNER, false);
        $this->get(route('buyersGuide.region', ['region' => 'uk']))->assertStatus(200)->assertDontSee(self::BANNER, false);
    }

    public function testShownOnTheLastDayAndGoneAfter()
    {
        $this->travelTo(\Carbon\Carbon::parse('2031-12-25 23:00:00'));
        $this->get(route('about.landing'))->assertSee(self::BANNER, false);

        $this->travelTo(\Carbon\Carbon::parse('2031-12-26 00:01:00'));
        $this->get(route('about.landing'))->assertDontSee(self::BANNER, false);
    }

    public function testOffWhenNoEndDate()
    {
        config(['buyers-guide.banner_until' => null]);
        $this->get(route('about.landing'))->assertDontSee(self::BANNER, false);
    }

    public function testNavLinksToTheGuide()
    {
        $this->get(route('about.landing'))->assertSee('<a href="'.route('buyersGuide.landing').'">Buyer\'s guide</a>', false);
    }
}
