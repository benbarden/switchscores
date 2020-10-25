<?php

namespace App\Construction\GameImportRule;

use App\GameImportRuleWikipedia;

class WikipediaDirector
{
    /**
     * @var WikipediaBuilder
     */
    private $builder;

    public function setBuilder(WikipediaBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function buildNew($params): void
    {
        $this->buildGameImportRule($params);
    }

    public function buildExisting(GameImportRuleWikipedia $gameImportRule, $params): void
    {
        $this->builder->setGameImportRule($gameImportRule);
        $this->buildGameImportRule($params);
    }

    public function buildGameImportRule($params): void
    {
        if (array_key_exists('game_id', $params)) {
            $this->builder->setGameId($params['game_id']);
        }

        if (array_key_exists('ignore_developers', $params)) {
            switch ($params['ignore_developers']) {
                case 'on':
                case 1:
                    $ignoreDevelopers = 1;
                    break;
                default:
                    $ignoreDevelopers = 0;
                    break;
            }
            $this->builder->setIgnoreDevelopers($ignoreDevelopers);
        } else {
            $this->builder->setIgnoreDevelopers(0);
        }

        if (array_key_exists('ignore_publishers', $params)) {
            switch ($params['ignore_publishers']) {
                case 'on':
                case 1:
                    $ignorePublishers = 1;
                    break;
                default:
                    $ignorePublishers = 0;
                    break;
            }
            $this->builder->setIgnorePublishers($ignorePublishers);
        } else {
            $this->builder->setIgnorePublishers(0);
        }

        if (array_key_exists('ignore_europe_dates', $params)) {
            switch ($params['ignore_europe_dates']) {
                case 'on':
                case 1:
                    $ignoreEuropeDates = 1;
                    break;
                default:
                    $ignoreEuropeDates = 0;
                    break;
            }
            $this->builder->setIgnoreEuropeDates($ignoreEuropeDates);
        } else {
            $this->builder->setIgnoreEuropeDates(0);
        }

        if (array_key_exists('ignore_us_dates', $params)) {
            switch ($params['ignore_us_dates']) {
                case 'on':
                case 1:
                    $ignoreUSDates = 1;
                    break;
                default:
                    $ignoreUSDates = 0;
                    break;
            }
            $this->builder->setIgnoreUSDates($ignoreUSDates);
        } else {
            $this->builder->setIgnoreUSDates(0);
        }

        if (array_key_exists('ignore_jp_dates', $params)) {
            switch ($params['ignore_jp_dates']) {
                case 'on':
                case 1:
                    $ignoreJPDates = 1;
                    break;
                default:
                    $ignoreJPDates = 0;
                    break;
            }
            $this->builder->setIgnoreJPDates($ignoreJPDates);
        } else {
            $this->builder->setIgnoreJPDates(0);
        }

        if (array_key_exists('ignore_genres', $params)) {
            switch ($params['ignore_genres']) {
                case 'on':
                case 1:
                    $ignoreGenres = 1;
                    break;
                default:
                    $ignoreGenres = 0;
                    break;
            }
            $this->builder->setIgnoreGenres($ignoreGenres);
        } else {
            $this->builder->setIgnoreGenres(0);
        }
    }
}
