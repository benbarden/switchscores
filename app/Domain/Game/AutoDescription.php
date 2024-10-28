<?php

namespace App\Domain\Game;

use App\Models\Game;
use App\Domain\Category\Blurb as CategoryBlurb;

class AutoDescription
{
    private $game;

    public function category()
    {
        $blurbCategory = new CategoryBlurb();
        $default = $blurbCategory->default();

        $blurb = '<strong>'.$this->game->title.'</strong> is ';

        if ($this->game->category) {
            if ($this->game->category->blurb_option) {
                $blurb .= $blurbCategory->parse($this->game->category).'. ';
            } else {
                $blurb .= $default;
            }
        } elseif ($this->game->eu_is_released == 1) {
            $blurb .= ' currently uncategorised. (Help us out!) ';
        } else {
            $blurb .= ' an upcoming game for the Nintendo Switch. ';
        }

        return $blurb;
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
        if (!$this->game->isDigitalDelisted() && ($this->game->game_rank)) {
            $blurb = 'It is ranked #'.$this->game->game_rank.' on the all-time Top Rated Switch games, '.
                ' with a total of '.$this->game->review_count.' reviews '.
                ' and an average score of '.$this->game->rating_avg.'. ';
            return $blurb;
        }
    }

    public function reviews()
    {
        if (!$this->game->isDigitalDelisted() && (!$this->game->game_rank) && ($this->game->eu_is_released == 1)) {
            switch ($this->game->review_count) {
                case 0:
                    $blurb = 'As it has no reviews, it is currently unranked. We need 3 reviews to give the game a rank. ';
                    break;
                case 1:
                    $blurb = 'As it only has 1 review, it is currently unranked. We need 2 more reviews to give the game a rank. ';
                    break;
                case 2:
                    $blurb = 'As it only has 2 reviews, it is currently unranked. We need 1 more review to give the game a rank. ';
                    break;
                default:
                    break;
            }
            return $blurb;
        }
    }

    public function series()
    {
        if ($this->game->series_id) {
            $series = $this->game->series->series;
            if (str_starts_with($series, 'The')) {
                $blurb = 'It is part of '.$series.' series. ';
            } else {
                $blurb = 'It is part of the '.$series.' series. ';
            }
            return $blurb;
        }
    }

    public function collection()
    {
        if ($this->game->collection_id) {
            $collection = $this->game->gameCollection->name;
            if (str_starts_with($collection, 'The')) {
                $blurb = 'It is part of '.$collection.' collection. ';
            } else {
                $blurb = 'It is part of the '.$collection.' collection. ';
            }
            return $blurb;
        }
    }

    public function generate(Game $game)
    {
        $this->game = $game;

        $category = $this->category();
        $delisted = $this->delisted();
        $ranking = $this->ranking();
        $reviews = $this->reviews();
        $series = $this->series();
        $collection = $this->collection();

        $blurb = $category.$delisted.$ranking.$reviews.$series.$collection;

        return $blurb;

    }
}