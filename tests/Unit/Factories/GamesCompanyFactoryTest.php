<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Factories\GamesCompanyFactory;

class GamesCompanyFactoryTest extends TestCase
{
    public function testSetNameAndLinkTitle()
    {
        $name = 'A Very Good Game Developer';
        $linkTitle = 'a-very-good-game-developer';
        $developer = GamesCompanyFactory::create(1, $name, $linkTitle);
        $this->assertEquals($name, $developer->name);
        $this->assertEquals($linkTitle, $developer->link_title);
    }

    public function testSetWebsiteUrl()
    {
        $name = 'A Very Good Game Developer';
        $linkTitle = 'a-very-good-game-developer';
        $websiteUrl = 'https://www.worldofswitch.com';
        $developer = GamesCompanyFactory::create(1, $name, $linkTitle, $websiteUrl);
        $this->assertEquals($websiteUrl, $developer->website_url);
    }

    public function testSetTwitterId()
    {
        $name = 'A Very Good Game Developer';
        $linkTitle = 'a-very-good-game-developer';
        $websiteUrl = 'https://www.worldofswitch.com';
        $twitterId = 'worldofswitch';
        $developer = GamesCompanyFactory::create(1, $name, $linkTitle, $websiteUrl, $twitterId);
        $this->assertEquals($twitterId, $developer->twitter_id);
    }
}
