<?php

namespace App\Domain\Game;

use App\Models\Game;

class FormatOptions
{
    public function getOptionsPhysical()
    {
        $options = [];
        $options[] = Game::FORMAT_AVAILABLE;
        $options[] = Game::FORMAT_INCLUDED_IN_BUNDLE;
        $options[] = Game::FORMAT_LIMITED_EDITION;
        $options[] = Game::FORMAT_NOT_AVAILABLE;

        return $options;
    }

    public function getOptionsDigital()
    {
        $options = [];
        $options[] = Game::FORMAT_AVAILABLE;
        $options[] = Game::FORMAT_DELISTED;
        $options[] = Game::FORMAT_NOT_AVAILABLE;

        return $options;
    }

    public function getOptionsDLC()
    {
        $options = [];
        $options[] = Game::FORMAT_AVAILABLE;
        $options[] = Game::FORMAT_NOT_AVAILABLE;

        return $options;
    }

    public function getOptionsDemo()
    {
        $options = [];
        $options[] = Game::FORMAT_AVAILABLE;
        $options[] = Game::FORMAT_NOT_AVAILABLE;

        return $options;
    }

}