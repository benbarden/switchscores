<?php

namespace App\Domain\Game;

use App\Models\Console;
use App\Models\Game;
use App\Domain\Category\Blurb as CategoryBlurb;

class AutoDescription
{
    private $game;

    public function category()
    {
        $blurbCategory = new CategoryBlurb();
        $default = $blurbCategory->default();

        $blurb = '';

        if ($this->game->category) {
            $blurb = '<strong>' . $this->game->title . '</strong> is ';
            if ($this->game->category->blurb_option) {
                $blurb .= $blurbCategory->parse($this->game->category, $this->game->console) . '. ';
            } else {
                $blurb .= $default;
            }
        } elseif ($this->game->is_low_quality == 1) {
            $blurb = '<strong>'.$this->game->title.'</strong> is a game';
            if ($this->game->console->id == Console::ID_SWITCH_1) {
                $blurb .= ' for Nintendo Switch 1. ';
            } elseif ($this->game->console->id == Console::ID_SWITCH_2) {
                $blurb .= ' for Nintendo Switch 2. ';
            } else {
                $blurb .= '.';
            }
        } elseif ($this->game->eu_is_released == 1) {
            $blurb = '<strong>'.$this->game->title.'</strong> is ';
            $blurb .= 'currently uncategorised. (Help us out!) ';
        } else {
            $blurb = '<strong>'.$this->game->title.'</strong> is ';
            $blurb .= 'an upcoming game';
            if ($this->game->console->id == Console::ID_SWITCH_1) {
                $blurb .= ' for Nintendo Switch 1. ';
            } elseif ($this->game->console->id == Console::ID_SWITCH_2) {
                $blurb .= ' for Nintendo Switch 2. ';
            } else {
                $blurb .= '.';
            }
        }

        return $blurb;
    }

    public function releaseDateAndPublishers()
    {
        if (!$this->game->eu_release_date) {
            return '';
        }
        if (count($this->game->gamePublishers) > 0) {
            $blurb = 'Released on '.date('jS F Y', strtotime($this->game->eu_release_date)).', ';
            $blurb .= 'it was published by ';
            foreach ($this->game->gamePublishers as $gamePublisher) {
                $pubName = $gamePublisher->publisher->name;
                $blurb .= $pubName;
                break;
            }
            $blurb .= '. ';
            return $blurb;
        } else {
            return 'It was released on '.date('jS F Y', strtotime($this->game->eu_release_date)).'. ';
        }
    }

    public function delisted()
    {
        if ($this->game->isDigitalDelisted()) {
            return 'It has been <strong>de-listed</strong> from the eShop. De-listed games are not included in our rankings.';
        } else {
            return '';
        }
    }

    public function ranking()
    {
        $blurb = '';
        if ($this->game->console_id == Console::ID_SWITCH_2) {
            $consoleDesc = Console::DESC_SWITCH_2;
        } else {
            $consoleDesc = Console::DESC_SWITCH_1;
        }
        if (!$this->game->isDigitalDelisted() && ($this->game->game_rank)) {
            $blurb = 'It is ranked #'.$this->game->game_rank.' on the all-time Top Rated '.$consoleDesc.' games, '.
                'with '.$this->game->review_count.' reviews '.
                'and an average score of '.$this->game->rating_avg.'. ';
        }
        return $blurb;
    }

    public function reviews()
    {
        $blurb = '';
        if (!$this->game->isDigitalDelisted() && (!$this->game->game_rank) && ($this->game->eu_is_released == 1) && ($this->game->is_low_quality == 0)) {
            switch ($this->game->review_count) {
                case 0:
                    $blurb = 'This game hasn\'t been ranked yet, as it doesn\'t have any reviews. We need 3 reviews to give it a rank. ';
                    break;
                case 1:
                    $blurb = 'This game hasn\'t been ranked yet, as it only has 1 review. We need 2 more reviews to give it a rank. ';
                    break;
                case 2:
                    $blurb = 'This game hasn\'t been ranked yet, as it only has 2 reviews. We need 1 more review to give it a rank. ';
                    break;
                default:
                    break;
            }
        }
        return $blurb;
    }

    public function series()
    {
        if ($this->game->series_id) {
            $series = $this->game->series->series;
            if (str_starts_with($series, 'The')) {
                $blurb = 'Part of '.$series.' series. ';
            } else {
                $blurb = 'Part of the '.$series.' series. ';
            }
            return $blurb;
        }
    }

    public function collection()
    {
        if ($this->game->collection_id) {
            $collection = $this->game->gameCollection->name;
            if (str_starts_with($collection, 'The')) {
                $blurb = 'Part of '.$collection.' collection. ';
            } else {
                $blurb = 'Part of the '.$collection.' collection. ';
            }
            return $blurb;
        }
    }

    public function generate(Game $game)
    {
        $this->game = $game;

        $category = $this->category();
        $releaseDateAndPubs = $this->releaseDateAndPublishers();
        $delisted = $this->delisted();
        $ranking = $this->ranking();
        $reviews = $this->reviews();
        $series = $this->series();
        $collection = $this->collection();

        $blurb = $category.$releaseDateAndPubs.$delisted.$ranking.$reviews.$series.$collection;

        return $blurb;

    }
}