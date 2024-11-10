<?php

namespace App\Domain\AffiliateCodes;

class Amazon
{
    const SWITCHSCORES_AMAZON_ID = 'switchscore07-21';

    public function getId()
    {
        return self::SWITCHSCORES_AMAZON_ID;
    }
}