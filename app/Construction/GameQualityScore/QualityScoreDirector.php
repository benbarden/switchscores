<?php

namespace App\Construction\GameQualityScore;

use App\DataSourceParsed;
use App\Game;
use App\GameImportRuleEshop;
use App\GameImportRuleWikipedia;
use App\GameQualityScore;

class QualityScoreDirector
{
    /**
     * @var QualityScoreBuilder
     */
    private $builder;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var integer
     */
    private $scoreRunningTotal;

    /**
     * @var GameImportRuleEshop
     */
    private $gameImportRuleEshop;

    /**
     * @var GameImportRuleWikipedia
     */
    private $gameImportRuleWikipedia;

    /**
     * @var DataSourceParsed
     */
    private $dsParsedNintendoCoUk;

    /**
     * @var DataSourceParsed
     */
    private $dsParsedWikipedia;

    public function __construct()
    {
        $this->scoreRunningTotal = 0;
    }

    public function setBuilder(QualityScoreBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function getGameQualityScore(): GameQualityScore
    {
        return $this->builder->getGameQualityScore();
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function setImportRuleEshop(GameImportRuleEshop $importRuleEshop): void
    {
        $this->gameImportRuleEshop = $importRuleEshop;
    }

    public function setImportRuleWikipedia(GameImportRuleWikipedia $importRuleWikipedia): void
    {
        $this->gameImportRuleWikipedia = $importRuleWikipedia;
    }

    public function setDataSourceParsedNintendoCoUk(DataSourceParsed $dataSourceParsed): void
    {
        $this->dsParsedNintendoCoUk = $dataSourceParsed;
    }

    public function setDataSourceParsedWikipedia(DataSourceParsed $dataSourceParsed): void
    {
        $this->dsParsedWikipedia = $dataSourceParsed;
    }

    public function buildNew(): void
    {
        $gameId = $this->game->id;
        $this->builder->setGameId($gameId);
        $this->buildGameQualityScore();
    }

    public function buildExisting(GameQualityScore $gameQualityScore): void
    {
        $this->builder->setGameQualityScore($gameQualityScore);
        $this->buildGameQualityScore();
    }

    public function buildGameQualityScore(): void
    {
        $maxScore = GameQualityScore::MAX_SCORE;
        $this->scoreRunningTotal = 0;

        if ($this->game->category_id != null) {
            $this->builder->setHasCategory(1);
            $this->scoreRunningTotal++;
        } else {
            $this->builder->setHasCategory(0);
        }

        if ($this->game->gameDevelopers->count() > 0) {
            $this->builder->setHasDevelopers(1);
            $this->scoreRunningTotal++;
        } else {
            $this->builder->setHasDevelopers(0);
        }

        if ($this->game->gamePublishers->count() > 0) {
            $this->builder->setHasPublishers(1);
            $this->scoreRunningTotal++;
        } else {
            $this->builder->setHasPublishers(0);
        }

        if ($this->game->players != null) {
            $this->builder->setHasPlayers(1);
            $this->scoreRunningTotal++;
        } else {
            $this->builder->setHasPlayers(0);
        }

        if ($this->game->price_eshop != null) {
            $this->builder->setHasPrice(1);
            $this->scoreRunningTotal++;
        } else {
            $this->builder->setHasPrice(0);
        }

        // Nintendo.co.uk
        $this->buildNintendoCoUkRules();

        // Wikipedia
        $this->buildWikipediaRules();


        // Set quality score
        $qualityScore = ($this->scoreRunningTotal / $maxScore) * 100;
        $qualityScore = number_format(round($qualityScore, 2), 2);
        $this->builder->setQualityScore($qualityScore);
    }

    public function buildNintendoCoUkRules(): void
    {
        if (!$this->dsParsedNintendoCoUk || !$this->gameImportRuleEshop) {

            // No import rule or data source record to compare against, so set all to pass
            $this->builder->setNoConflictNintendoEUReleaseDate(1);
            $this->builder->setNoConflictNintendoPrice(1);
            $this->builder->setNoConflictNintendoPlayers(1);
            $this->builder->setNoConflictNintendoPublishers(1);
            $this->builder->setNoConflictNintendoGenre(1);
            $this->scoreRunningTotal = $this->scoreRunningTotal + 5;

        } else {

            $this->buildNintendoCoUkRuleEUReleaseDate();
            $this->buildNintendoCoUkRulePrice();
            $this->buildNintendoCoUkRulePlayers();
            $this->buildNintendoCoUkRulePublishers();
            $this->buildNintendoCoUkRuleGenre();

        }
    }

    public function buildNintendoCoUkRuleEUReleaseDate(): void
    {
        if ($this->gameImportRuleEshop->shouldIgnoreEuropeDates()) {
            $this->builder->setNoConflictNintendoEUReleaseDate(1);
            $this->scoreRunningTotal++;
        } else {
            if ($this->game->eu_release_date == $this->dsParsedNintendoCoUk->release_date_eu) {
                $this->builder->setNoConflictNintendoEUReleaseDate(1);
                $this->scoreRunningTotal++;
            } else {
                $this->builder->setNoConflictNintendoEUReleaseDate(0);
            }
        }
    }

    public function buildNintendoCoUkRulePrice(): void
    {
        if ($this->gameImportRuleEshop->shouldIgnorePrice()) {
            $this->builder->setNoConflictNintendoPrice(1);
            $this->scoreRunningTotal++;
        } else {
            if ($this->game->price_eshop == $this->dsParsedNintendoCoUk->price_standard) {
                $this->builder->setNoConflictNintendoPrice(1);
                $this->scoreRunningTotal++;
            } else {
                $this->builder->setNoConflictNintendoPrice(0);
            }
        }
    }

    public function buildNintendoCoUkRulePlayers(): void
    {
        if ($this->gameImportRuleEshop->shouldIgnorePlayers()) {
            $this->builder->setNoConflictNintendoPlayers(1);
            $this->scoreRunningTotal++;
        } else {
            if ($this->game->players == $this->dsParsedNintendoCoUk->players) {
                $this->builder->setNoConflictNintendoPlayers(1);
                $this->scoreRunningTotal++;
            } else {
                $this->builder->setNoConflictNintendoPlayers(0);
            }
        }
    }

    public function buildNintendoCoUkRulePublishers(): void
    {
        // @todo
        $this->builder->setNoConflictNintendoPublishers(1);
        $this->scoreRunningTotal++;
    }

    public function buildNintendoCoUkRuleGenre(): void
    {
        // @todo
        $this->builder->setNoConflictNintendoGenre(1);
        $this->scoreRunningTotal++;
    }

    public function buildWikipediaRules(): void
    {
        if (!$this->dsParsedWikipedia || !$this->gameImportRuleWikipedia) {

            // No import rule or data source record to compare against, so set all to pass
            $this->builder->setNoConflictWikipediaEUReleaseDate(1);
            $this->builder->setNoConflictWikipediaUSReleaseDate(1);
            $this->builder->setNoConflictWikipediaJPReleaseDate(1);
            $this->builder->setNoConflictWikipediaDevelopers(1);
            $this->builder->setNoConflictWikipediaPublishers(1);
            $this->builder->setNoConflictWikipediaGenre(1);
            $this->scoreRunningTotal = $this->scoreRunningTotal + 6;

        } else {

            $this->buildWikipediaRuleEUReleaseDate();
            $this->buildWikipediaRuleUSReleaseDate();
            $this->buildWikipediaRuleJPReleaseDate();
            $this->buildWikipediaRuleDevelopers();
            $this->buildWikipediaRulePublishers();
            $this->buildWikipediaRuleGenre();

        }
    }

    public function buildWikipediaRuleEUReleaseDate(): void
    {
        if ($this->gameImportRuleWikipedia->shouldIgnoreEuropeDates()) {
            $this->builder->setNoConflictWikipediaEUReleaseDate(1);
            $this->scoreRunningTotal++;
        } else {
            if ($this->game->eu_release_date == $this->dsParsedWikipedia->release_date_eu) {
                $this->builder->setNoConflictWikipediaEUReleaseDate(1);
                $this->scoreRunningTotal++;
            } else {
                $this->builder->setNoConflictWikipediaEUReleaseDate(0);
            }
        }
    }

    public function buildWikipediaRuleUSReleaseDate(): void
    {
        if ($this->gameImportRuleWikipedia->shouldIgnoreUSDates()) {
            $this->builder->setNoConflictWikipediaUSReleaseDate(1);
            $this->scoreRunningTotal++;
        } else {
            if ($this->game->us_release_date == $this->dsParsedWikipedia->release_date_us) {
                $this->builder->setNoConflictWikipediaUSReleaseDate(1);
                $this->scoreRunningTotal++;
            } else {
                $this->builder->setNoConflictWikipediaUSReleaseDate(0);
            }
        }
    }

    public function buildWikipediaRuleJPReleaseDate(): void
    {
        if ($this->gameImportRuleWikipedia->shouldIgnoreJPDates()) {
            $this->builder->setNoConflictWikipediaJPReleaseDate(1);
            $this->scoreRunningTotal++;
        } else {
            if ($this->game->jp_release_date == $this->dsParsedWikipedia->release_date_jp) {
                $this->builder->setNoConflictWikipediaJPReleaseDate(1);
                $this->scoreRunningTotal++;
            } else {
                $this->builder->setNoConflictWikipediaJPReleaseDate(0);
            }
        }
    }

    public function buildWikipediaRuleDevelopers(): void
    {
        // @todo
        $this->builder->setNoConflictWikipediaDevelopers(1);
        $this->scoreRunningTotal++;
    }

    public function buildWikipediaRulePublishers(): void
    {
        // @todo
        $this->builder->setNoConflictWikipediaPublishers(1);
        $this->scoreRunningTotal++;
    }

    public function buildWikipediaRuleGenre(): void
    {
        // @todo
        $this->builder->setNoConflictWikipediaGenre(1);
        $this->scoreRunningTotal++;
    }

}