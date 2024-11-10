<?php

namespace App\Domain\AffiliateCodes;

class Amazon
{
    const AMAZON_UK_ID = 'switchscore07-21';

    const AMAZON_US_ID = 'switchscores-20';

    public function getUKId()
    {
        return self::AMAZON_UK_ID;
    }

    public function getUSId()
    {
        return self::AMAZON_US_ID;
    }
}