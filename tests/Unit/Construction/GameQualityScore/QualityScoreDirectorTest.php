<?php

namespace Tests\Unit\Construction\GameQualityScore;

use App\Construction\GameQualityScore\QualityScoreBuilder;
use App\Construction\GameQualityScore\QualityScoreDirector;
use App\DataSourceParsed;
use App\Game;
use App\GameDeveloper;
use App\GamePublisher;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class QualityScoreDirectorTest extends TestCase
{
    const MAX_SCORE = 16;

    public function getExpectedScore($passes)
    {
        $score = ($passes / self::MAX_SCORE) * 100;
        $score = number_format(round($score, 2), 2);
        return $score;
    }

    public function testBuildHasCategoryPass()
    {
        $game = new Game;
        $game->category_id = 1;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(1, $qualityScore->has_category);
        $this->assertEquals(75.00, $qualityScore->quality_score);
    }

    public function testBuildHasCategoryFail()
    {
        $game = new Game;
        $game->category_id = null;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(0, $qualityScore->has_category);
        $this->assertEquals(68.75, $qualityScore->quality_score);
    }

    public function testBuildHasDevelopersPass()
    {
        $game = new Game;
        $gameDeveloper = new GameDeveloper;
        $gameDeveloper->developer_id = 1;
        $game->gameDevelopers = $gameDeveloper;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(1, $qualityScore->has_developers);
        $this->assertEquals(75.00, $qualityScore->quality_score);
    }

    public function testBuildHasDevelopersFail()
    {
        $game = new Game;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(0, $qualityScore->has_developers);
        $this->assertEquals(68.75, $qualityScore->quality_score);
    }

    public function testBuildHasPublishersPass()
    {
        $game = new Game;
        $gamePublisher = new GamePublisher;
        $gamePublisher->publisher_id = 1;
        $game->gamePublishers = $gamePublisher;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(1, $qualityScore->has_publishers);
        $this->assertEquals(75.00, $qualityScore->quality_score);
    }

    public function testBuildHasPublishersFail()
    {
        $game = new Game;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(0, $qualityScore->has_publishers);
        $this->assertEquals(68.75, $qualityScore->quality_score);
    }

    public function testBuildHasPlayersPass()
    {
        $game = new Game;
        $game->players = "1";

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(1, $qualityScore->has_players);
        $this->assertEquals(75.00, $qualityScore->quality_score);
    }

    public function testBuildHasPlayersFail()
    {
        $game = new Game;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(0, $qualityScore->has_players);
        $this->assertEquals(68.75, $qualityScore->quality_score);
    }

    public function testBuildHasPricePass()
    {
        $game = new Game;
        $game->price_eshop = 6.29;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(1, $qualityScore->has_price);
        $this->assertEquals(75.00, $qualityScore->quality_score);
    }

    public function testBuildHasPriceFail()
    {
        $game = new Game;

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(0, $qualityScore->has_price);
        $this->assertEquals(68.75, $qualityScore->quality_score);
    }

    public function testBuildNintendoCoUkPublishersNaturalOrder()
    {
        $gamePublishers = new Collection();

        $game = new Game;
        $gamePublisherTTGames = new GamePublisher(['publisher_id' => 101]);
        $gamePublisherUnfinishedPixel = new GamePublisher(['publisher_id' => 102]);
        $gamePublishers->push($gamePublisherTTGames);
        $gamePublishers->push($gamePublisherUnfinishedPixel);
        $game->gamePublishers = $gamePublishers;
        $dsParsedNintendoCoUk = new DataSourceParsed;
        $dsParsedNintendoCoUk->publishers = 'TT Games,Unfinished Pixel';

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->setDataSourceParsedNintendoCoUk($dsParsedNintendoCoUk);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(1, $qualityScore->has_publishers);
        $this->assertEquals(1, $qualityScore->no_conflict_nintendo_publishers);
        $this->assertEquals(75.00, $qualityScore->quality_score);
    }

    public function testBuildNintendoCoUkPublishersReverseOrderGamePub()
    {
        $gamePublishers = new Collection();

        $game = new Game;
        $gamePublisherTTGames = new GamePublisher(['publisher_id' => 101]);
        $gamePublisherUnfinishedPixel = new GamePublisher(['publisher_id' => 102]);
        $gamePublishers->push($gamePublisherUnfinishedPixel);
        $gamePublishers->push($gamePublisherTTGames);
        $game->gamePublishers = $gamePublishers;
        $dsParsedNintendoCoUk = new DataSourceParsed;
        $dsParsedNintendoCoUk->publishers = 'TT Games,Unfinished Pixel';

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->setDataSourceParsedNintendoCoUk($dsParsedNintendoCoUk);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(1, $qualityScore->has_publishers);
        $this->assertEquals(1, $qualityScore->no_conflict_nintendo_publishers);
        $this->assertEquals(75.00, $qualityScore->quality_score);
    }

    public function testBuildNintendoCoUkPublishersReverseOrderDataSource()
    {
        $gamePublishers = new Collection();

        $game = new Game;
        $gamePublisherTTGames = new GamePublisher(['publisher_id' => 101]);
        $gamePublisherUnfinishedPixel = new GamePublisher(['publisher_id' => 102]);
        $gamePublishers->push($gamePublisherTTGames);
        $gamePublishers->push($gamePublisherUnfinishedPixel);
        $game->gamePublishers = $gamePublishers;
        $dsParsedNintendoCoUk = new DataSourceParsed;
        $dsParsedNintendoCoUk->publishers = 'Unfinished Pixel,TT Games';

        $director = new QualityScoreDirector();
        $builder = new QualityScoreBuilder();
        $director->setBuilder($builder);

        $director->setGame($game);
        $director->setDataSourceParsedNintendoCoUk($dsParsedNintendoCoUk);
        $director->buildNew();
        $qualityScore = $builder->getGameQualityScore();

        $this->assertEquals(1, $qualityScore->has_publishers);
        $this->assertEquals(1, $qualityScore->no_conflict_nintendo_publishers);
        $this->assertEquals(75.00, $qualityScore->quality_score);
    }
}
