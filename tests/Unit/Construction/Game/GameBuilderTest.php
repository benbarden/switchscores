<?php

namespace Tests\Unit\Construction\Game;

use Tests\TestCase;

use App\Construction\Game\GameBuilder;

class GameBuilderTest extends TestCase
{
    public function testTitle()
    {
        $title = 'Yoshi';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder->setTitle($title)->getGame();
        $this->assertEquals($title, $game->title);
    }

    public function testLinkTitle()
    {
        $title = 'Yoshi';
        $linkTitle = 'yoshi-game-title';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setLinkTitle($linkTitle)
            ->getGame();
        $this->assertEquals($linkTitle, $game->link_title);
    }

    public function testPriceEshop()
    {
        $title = 'Yoshi';
        $priceEshop = '7.59';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setPriceEshop($priceEshop)
            ->getGame();
        $this->assertEquals($priceEshop, $game->price_eshop);
    }

    public function testPlayers()
    {
        $title = 'Yoshi';
        $players = '2-4';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setPlayers($players)
            ->getGame();
        $this->assertEquals($players, $game->players);
    }

    public function testReviewCount()
    {
        $title = 'Yoshi';
        $reviewCount = 10;

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setReviewCount($reviewCount)
            ->getGame();
        $this->assertEquals($reviewCount, $game->review_count);
    }

    public function testAmazonUkLink()
    {
        $title = 'Yoshi';
        $amazonUkLink = 'https://amazon.co.uk/blahblah';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setAmazonUkLink($amazonUkLink)
            ->getGame();
        $this->assertEquals($amazonUkLink, $game->amazon_uk_link);
    }

    public function testVideoUrl()
    {
        $title = 'Yoshi';
        $videoUrl = 'https://youtube.com/blahblah';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setVideoUrl($videoUrl)
            ->getGame();
        $this->assertEquals($videoUrl, $game->video_url);
    }

    public function testBoxartSquareUrl()
    {
        $title = 'Yoshi';
        $boxartSquareUrl = 'yoshi-boxart-square.jpg';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setBoxartSquareUrl($boxartSquareUrl)
            ->getGame();
        $this->assertEquals($boxartSquareUrl, $game->boxart_square_url);
    }

    public function testEshopEuropeFsId()
    {
        $title = 'Yoshi';
        $fsId = 7575757;

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setEshopEuropeFsId($fsId)
            ->getGame();
        $this->assertEquals($fsId, $game->eshop_europe_fs_id);
    }

    public function testBoxartHeaderImage()
    {
        $title = 'Yoshi';
        $boxartHeaderImage = 'yoshi-boxart-header.jpg';

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setBoxartHeaderImage($boxartHeaderImage)
            ->getGame();
        $this->assertEquals($boxartHeaderImage, $game->boxart_header_image);
    }

    public function testRatingAvg()
    {
        $title = 'Yoshi';
        $ratingAvg = 7.5;

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setRatingAvg($ratingAvg)
            ->getGame();
        $this->assertEquals($ratingAvg, $game->rating_avg);
    }

    public function testGameRank()
    {
        $title = 'Yoshi';
        $gameRank = 129;

        $gameBuilder = new GameBuilder();
        $game = $gameBuilder
            ->setTitle($title)
            ->setGameRank($gameRank)
            ->getGame();
        $this->assertEquals($gameRank, $game->game_rank);
    }
}
