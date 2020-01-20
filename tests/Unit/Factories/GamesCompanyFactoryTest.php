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
        $developer = GamesCompanyFactory::createActive($name, $linkTitle);
        $this->assertEquals($name, $developer->name);
        $this->assertEquals($linkTitle, $developer->link_title);
    }

    public function testSetWebsiteUrl()
    {
        $name = 'A Very Good Game Developer';
        $linkTitle = 'a-very-good-game-developer';
        $websiteUrl = 'https://www.switchscores.com';
        $developer = GamesCompanyFactory::createActive($name, $linkTitle, $websiteUrl);
        $this->assertEquals($websiteUrl, $developer->website_url);
    }

    public function testSetTwitterId()
    {
        $name = 'A Very Good Game Developer';
        $linkTitle = 'a-very-good-game-developer';
        $websiteUrl = 'https://www.switchscores.com';
        $twitterId = 'switchscores';
        $developer = GamesCompanyFactory::createActive($name, $linkTitle, $websiteUrl, $twitterId);
        $this->assertEquals($twitterId, $developer->twitter_id);
    }
}
