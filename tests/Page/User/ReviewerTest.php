<?php

namespace Tests\Page\User;

use App\Models\User;
use Tests\TestCase;

class ReviewerTest extends TestCase
{
    /**
     * @var User
     */
    private $activeUser;

    private $userEmail;

    public function setUp(): void
    {
        parent::setUp();

        $userEmail = 'the.amazing.review.person@switchscores.com';
        $this->userEmail = $userEmail;

        $this->activeUser = new User([
            'display_name' => 'King Reviewer of Reviewerville',
            'email' => $userEmail,
            'partner_id' => 1
        ]);
    }

    public function tearDown(): void
    {
        User::where('email', $this->userEmail)->delete();
        unset($this->activeUser);
        parent::tearDown();
    }

    public function doPageTest($url)
    {
        $response = $this->get($url);
        $response->assertStatus(200);
    }

    public function testUserPages()
    {
        $this->be($this->activeUser);
        $this->doPageTest("/reviewers");
        $this->doPageTest("/reviewers/reviews/review-draft/find-game");
        $this->doPageTest("/reviewers/reviews/review-draft/add/1");
        $this->doPageTest("/reviewers/reviews/review-draft/edit/1");
        $this->doPageTest("/reviewers/campaigns/1");
        $this->doPageTest("/reviewers/games/1");
        $this->doPageTest("/reviewers/stats");
        $this->doPageTest("/reviewers/feed-health");
        $this->doPageTest("/reviewers/feed-health/by-process-status/Not in database");
        $this->doPageTest("/reviewers/feed-health/by-parse-status/Manually linked by reviewer");
        $this->doPageTest("/reviewers/reviews");
        $this->doPageTest("/reviewers/reviews/10");
        //$this->doPageTest("/reviewers/unranked-games");
        $this->doPageTest("/reviewers/unranked-games/by-year/2017");
        $this->doPageTest("/reviewers/unranked-games/by-year/2018");
        $this->doPageTest("/reviewers/unranked-games/by-year/2019");
        //$this->doPageTest("/reviewers/unranked-games/by-year/2020");
        //$this->doPageTest("/reviewers/unranked-games/by-year/2021");
        $this->doPageTest("/reviewers/unranked-games/by-count/0");
        $this->doPageTest("/reviewers/unranked-games/by-count/1");
        $this->doPageTest("/reviewers/unranked-games/by-count/2");
        $this->doPageTest("/reviewers/unranked-games/by-list/aca-neogeo");
        $this->doPageTest("/reviewers/unranked-games/by-list/arcade-archives");
        $this->doPageTest("/reviewers/unranked-games/by-list/all-others");
    }
}
