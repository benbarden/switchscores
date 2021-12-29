<?php

namespace App\Construction\GameImportRule;

use App\GameImportRuleEshop;

class EshopDirector
{
    /**
     * @var Builder
     */
    private $builder;

    public function setBuilder(Builder $builder): void
    {
        $this->builder = $builder;
    }

    public function buildNew($params): void
    {
        $this->buildGameImportRule($params);
    }

    public function buildExisting(GameImportRuleEshop $gameImportRule, $params): void
    {
        $this->builder->setGameImportRule($gameImportRule);
        $this->buildGameImportRule($params);
    }

    public function buildGameImportRule($params): void
    {
        if (array_key_exists('game_id', $params)) {
            $this->builder->setGameId($params['game_id']);
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

        if (array_key_exists('ignore_price', $params)) {
            switch ($params['ignore_price']) {
                case 'on':
                case 1:
                    $ignorePrice = 1;
                    break;
                default:
                    $ignorePrice = 0;
                    break;
            }
            $this->builder->setIgnorePrice($ignorePrice);
        } else {
            $this->builder->setIgnorePrice(0);
        }

        if (array_key_exists('ignore_players', $params)) {
            switch ($params['ignore_players']) {
                case 'on':
                case 1:
                    $ignorePlayers = 1;
                    break;
                default:
                    $ignorePlayers = 0;
                    break;
            }
            $this->builder->setIgnorePlayers($ignorePlayers);
        } else {
            $this->builder->setIgnorePlayers(0);
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
