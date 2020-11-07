<?php

namespace Tests\Unit\Construction\GameQualityScore;

use Tests\TestCase;

use App\Construction\GameQualityScore\QualityScoreBuilder;

class QualityScoreBuilderTest extends TestCase
{
    public function testHasCategory()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setHasCategory(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->has_category);
    }

    public function testHasDevelopers()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setHasDevelopers(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->has_developers);
    }

    public function testHasPublishers()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setHasPublishers(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->has_publishers);
    }

    public function testHasPlayers()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setHasPlayers(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->has_players);
    }


    public function testHasPrice()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setHasPrice(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->has_price);
    }

    public function testNoConflictNintendoEUReleaseDate()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictNintendoEUReleaseDate(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_nintendo_eu_release_date);
    }

    public function testNoConflictNintendoPrice()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictNintendoPrice(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_nintendo_price);
    }

    public function testNoConflictNintendoPlayers()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictNintendoPlayers(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_nintendo_players);
    }

    public function testNoConflictNintendoPublishers()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictNintendoPublishers(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_nintendo_publishers);
    }

    public function testNoConflictNintendoGenre()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictNintendoGenre(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_nintendo_genre);
    }

    public function testNoConflictWikipediaEUReleaseDate()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictWikipediaEUReleaseDate(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_wikipedia_eu_release_date);
    }

    public function testNoConflictWikipediaUSReleaseDate()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictWikipediaUSReleaseDate(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_wikipedia_us_release_date);
    }

    public function testNoConflictWikipediaJPReleaseDate()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictWikipediaJPReleaseDate(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_wikipedia_jp_release_date);
    }

    public function testNoConflictWikipediaDevelopers()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictWikipediaDevelopers(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_wikipedia_developers);
    }

    public function testNoConflictWikipediaPublishers()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictWikipediaPublishers(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_wikipedia_publishers);
    }

    public function testNoConflictWikipediaGenre()
    {
        $builder = new QualityScoreBuilder();
        $qualityScore = $builder->setNoConflictWikipediaGenre(1)->getGameQualityScore();
        $this->assertEquals(1, $qualityScore->no_conflict_wikipedia_genre);
    }
}
