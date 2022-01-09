<?php

namespace App\Construction\GameQualityScore;

use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use App\Models\GameImportRuleWikipedia;
use App\Models\GameQualityScore;
use Illuminate\Log\Logger;

class QualityScoreDirector
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var QualityScoreBuilder
     */
    private $builder;

    /**
     * @var \App\Models\Game
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
     * @var \App\Models\GameImportRuleWikipedia
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

    public function __construct($logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        }
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

    public function incrementScore($byHowMuch = 1)
    {
        $this->scoreRunningTotal = $this->scoreRunningTotal + $byHowMuch;
        if ($this->logger) {
            $this->logger->info('Running total: '.$this->scoreRunningTotal);
        }
    }

    public function calculateQualityScore()
    {
        $maxScore = GameQualityScore::MAX_SCORE;
        $qualityScore = ($this->scoreRunningTotal / $maxScore) * 100;
        $qualityScore = number_format(round($qualityScore, 2), 2);
        if ($this->logger) {
            $this->logger->info('Calculated quality score to be: '.$qualityScore);
        }
        return $qualityScore;
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
        $this->scoreRunningTotal = 0;

        if ($this->game->category_id != null) {
            $this->builder->setHasCategory(1);
            $this->incrementScore();
        } else {
            $this->builder->setHasCategory(0);
        }

        if ($this->game->gameDevelopers->count() > 0) {
            $this->builder->setHasDevelopers(1);
            $this->incrementScore();
        } else {
            $this->builder->setHasDevelopers(0);
        }

        if ($this->game->gamePublishers->count() > 0) {
            $this->builder->setHasPublishers(1);
            $this->incrementScore();
        } else {
            $this->builder->setHasPublishers(0);
        }

        if ($this->game->players != null) {
            $this->builder->setHasPlayers(1);
            $this->incrementScore();
        } else {
            $this->builder->setHasPlayers(0);
        }

        if ($this->game->price_eshop != null) {
            $this->builder->setHasPrice(1);
            $this->incrementScore();
        } else {
            $this->builder->setHasPrice(0);
        }

        // Nintendo.co.uk
        $this->buildNintendoCoUkRules();

        // Wikipedia
        $this->buildWikipediaRules();


        // Set quality score
        $qualityScore = $this->calculateQualityScore();
        $this->builder->setQualityScore($qualityScore);
    }

    public function buildNintendoCoUkRules(): void
    {
        if (!$this->dsParsedNintendoCoUk) {

            // No data source record to compare against, so set all to pass
            $this->builder->setNoConflictNintendoEUReleaseDate(1);
            $this->builder->setNoConflictNintendoPrice(1);
            $this->builder->setNoConflictNintendoPlayers(1);
            $this->builder->setNoConflictNintendoPublishers(1);
            $this->builder->setNoConflictNintendoGenre(1);
            $this->incrementScore(5);

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
        if ($this->gameImportRuleEshop) {
            $importRuleIgnore = $this->gameImportRuleEshop->shouldIgnoreEuropeDates();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictNintendoEUReleaseDate(1);
            $this->incrementScore();
        } else {
            if ($this->game->eu_release_date == $this->dsParsedNintendoCoUk->release_date_eu) {
                $this->builder->setNoConflictNintendoEUReleaseDate(1);
                $this->incrementScore();
            } else {
                $this->builder->setNoConflictNintendoEUReleaseDate(0);
            }
        }
    }

    public function buildNintendoCoUkRulePrice(): void
    {
        if ($this->gameImportRuleEshop) {
            $importRuleIgnore = $this->gameImportRuleEshop->shouldIgnorePrice();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictNintendoPrice(1);
            $this->incrementScore();
        } else {
            if ($this->game->price_eshop == $this->dsParsedNintendoCoUk->price_standard) {
                $this->builder->setNoConflictNintendoPrice(1);
                $this->incrementScore();
            } else {
                $this->builder->setNoConflictNintendoPrice(0);
            }
        }
    }

    public function buildNintendoCoUkRulePlayers(): void
    {
        if ($this->gameImportRuleEshop) {
            $importRuleIgnore = $this->gameImportRuleEshop->shouldIgnorePlayers();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictNintendoPlayers(1);
            $this->incrementScore();
        } else {
            if ($this->game->players == $this->dsParsedNintendoCoUk->players) {
                $this->builder->setNoConflictNintendoPlayers(1);
                $this->incrementScore();
            } else {
                $this->builder->setNoConflictNintendoPlayers(0);
            }
        }
    }

    public function buildNintendoCoUkRulePublishers(): void
    {
        if ($this->gameImportRuleEshop) {
            $importRuleIgnore = $this->gameImportRuleEshop->shouldIgnorePublishers();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictNintendoPublishers(1);
            $this->incrementScore();
        } else {

            if ($this->game->gamePublishers->count() > 0) {

                $gamePublisherArray = [];
                foreach ($this->game->gamePublishers as $gamePublisher) {
                    $gamePublisherArray[] = $gamePublisher->publisher->name;
                }
                sort($gamePublisherArray);
                $gamePublisherNames = implode(',', $gamePublisherArray);

                $dsPublishers = $this->dsParsedNintendoCoUk->publishers;
                $dsPublisherArray = explode(",", $dsPublishers);
                sort($dsPublisherArray);
                $dsPublisherNames = implode(",", $dsPublisherArray);

                if ($gamePublisherNames == $dsPublisherNames) {
                    $this->builder->setNoConflictNintendoPublishers(1);
                    $this->incrementScore();
                } else {
                    $this->builder->setNoConflictNintendoPublishers(0);
                }

            } else {

                // Fail if none set
                $this->builder->setNoConflictNintendoPublishers(0);

            }

        }
    }

    public function buildNintendoCoUkRuleGenre(): void
    {
        if ($this->gameImportRuleEshop) {
            $importRuleIgnore = $this->gameImportRuleEshop->shouldIgnoreGenres();
        } else {
            $importRuleIgnore = false;
        }

        // @todo
        $this->builder->setNoConflictNintendoGenre(1);
        $this->incrementScore();
    }

    public function buildWikipediaRules(): void
    {
        if (!$this->dsParsedWikipedia) {

            // No import rule or data source record to compare against, so set all to pass
            $this->builder->setNoConflictWikipediaEUReleaseDate(1);
            $this->builder->setNoConflictWikipediaUSReleaseDate(1);
            $this->builder->setNoConflictWikipediaJPReleaseDate(1);
            $this->builder->setNoConflictWikipediaDevelopers(1);
            $this->builder->setNoConflictWikipediaPublishers(1);
            $this->builder->setNoConflictWikipediaGenre(1);
            $this->incrementScore(6);

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
        if ($this->gameImportRuleWikipedia) {
            $importRuleIgnore = $this->gameImportRuleWikipedia->shouldIgnoreEuropeDates();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictWikipediaEUReleaseDate(1);
            $this->incrementScore();
        } else {
            if ($this->game->eu_release_date == $this->dsParsedWikipedia->release_date_eu) {
                $this->builder->setNoConflictWikipediaEUReleaseDate(1);
                $this->incrementScore();
            } else {
                $this->builder->setNoConflictWikipediaEUReleaseDate(0);
            }
        }
    }

    public function buildWikipediaRuleUSReleaseDate(): void
    {
        if ($this->gameImportRuleWikipedia) {
            $importRuleIgnore = $this->gameImportRuleWikipedia->shouldIgnoreUSDates();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictWikipediaUSReleaseDate(1);
            $this->incrementScore();
        } else {
            if ($this->game->us_release_date == $this->dsParsedWikipedia->release_date_us) {
                $this->builder->setNoConflictWikipediaUSReleaseDate(1);
                $this->incrementScore();
            } else {
                $this->builder->setNoConflictWikipediaUSReleaseDate(0);
            }
        }
    }

    public function buildWikipediaRuleJPReleaseDate(): void
    {
        if ($this->gameImportRuleWikipedia) {
            $importRuleIgnore = $this->gameImportRuleWikipedia->shouldIgnoreJPDates();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictWikipediaJPReleaseDate(1);
            $this->incrementScore();
        } else {
            if ($this->game->jp_release_date == $this->dsParsedWikipedia->release_date_jp) {
                $this->builder->setNoConflictWikipediaJPReleaseDate(1);
                $this->incrementScore();
            } else {
                $this->builder->setNoConflictWikipediaJPReleaseDate(0);
            }
        }
    }

    public function buildWikipediaRuleDevelopers(): void
    {
        if ($this->gameImportRuleWikipedia) {
            $importRuleIgnore = $this->gameImportRuleWikipedia->shouldIgnoreDevelopers();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictWikipediaDevelopers(1);
            $this->incrementScore();
        } else {

            if ($this->game->gameDevelopers->count() > 0) {

                $gameDeveloperArray = [];
                foreach ($this->game->gameDevelopers as $gameDeveloper) {
                    $gameDeveloperArray[] = $gameDeveloper->developer->name;
                }
                sort($gameDeveloperArray);
                $gameDeveloperNames = implode(',', $gameDeveloperArray);

                $dsDevelopers = $this->dsParsedWikipedia->developers;
                $dsDeveloperArray = explode(",", $dsDevelopers);
                sort($dsDeveloperArray);
                $dsDeveloperNames = implode(",", $dsDeveloperArray);

                if ($gameDeveloperNames == $dsDeveloperNames) {
                    $this->builder->setNoConflictWikipediaDevelopers(1);
                    $this->incrementScore();
                } else {
                    $this->builder->setNoConflictWikipediaDevelopers(0);
                }

            } else {

                // Fail if none set
                $this->builder->setNoConflictWikipediaDevelopers(0);

            }

        }
    }

    public function buildWikipediaRulePublishers(): void
    {
        if ($this->gameImportRuleWikipedia) {
            $importRuleIgnore = $this->gameImportRuleWikipedia->shouldIgnorePublishers();
        } else {
            $importRuleIgnore = false;
        }

        if ($importRuleIgnore) {
            $this->builder->setNoConflictWikipediaPublishers(1);
            $this->incrementScore();
        } else {

            if ($this->game->gamePublishers->count() > 0) {

                $gamePublisherArray = [];
                foreach ($this->game->gamePublishers as $gamePublisher) {
                    $gamePublisherArray[] = $gamePublisher->publisher->name;
                }
                sort($gamePublisherArray);
                $gamePublisherNames = implode(',', $gamePublisherArray);

                $dsPublishers = $this->dsParsedWikipedia->publishers;
                $dsPublisherArray = explode(",", $dsPublishers);
                sort($dsPublisherArray);
                $dsPublisherNames = implode(",", $dsPublisherArray);

                if ($gamePublisherNames == $dsPublisherNames) {
                    $this->builder->setNoConflictWikipediaPublishers(1);
                    $this->incrementScore();
                } else {
                    $this->builder->setNoConflictWikipediaPublishers(0);
                }

            } else {

                // Fail if none set
                $this->builder->setNoConflictWikipediaPublishers(0);

            }

        }
    }

    public function buildWikipediaRuleGenre(): void
    {
        if ($this->gameImportRuleWikipedia) {
            $importRuleIgnore = $this->gameImportRuleWikipedia->shouldIgnoreGenres();
        } else {
            $importRuleIgnore = false;
        }

        // @todo
        $this->builder->setNoConflictWikipediaGenre(1);
        $this->incrementScore();
    }

}