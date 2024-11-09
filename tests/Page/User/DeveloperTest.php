<?php

namespace Tests\Page\User;

use App\Models\User;
use Tests\TestCase;

class DeveloperTest extends TestCase
{
    /**
     * @var User
     */
    private $activeUser;

    private $userEmail;

    public function setUp(): void
    {
        parent::setUp();

        $userEmail = 'developer.developing.the.shiniest.developments@switchscores.com';
        $this->userEmail = $userEmail;

        $this->activeUser = new User([
            'display_name' => 'His Royal Developerness of Developertown',
            'email' => $userEmail,
            'is_developer' => 1
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
        $this->doPageTest("/user/developers");
        $this->doPageTest("/user/developers/switch-weekly");
    }
}
