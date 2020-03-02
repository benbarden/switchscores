<?php


namespace App\Services\HtmlLoader\Wikipedia;

use App\FeedItemGame;
use App\Services\HtmlLoader\Wikipedia\DateHandler;


class Skipper
{
    const DATE_TBA = 'TBA';
    const DATE_UNRELEASED = 'Unreleased';

    protected $titlesToSkip = [
        'Untitled ',
        '(tentative title)',
        'Nintendo Labo',
        'Schlag den Star: Das Spiel',
    ];

    public function countDatesTBAOrUnreleased(FeedItemGame $feedItemGame)
    {
        $datesToCheck = [self::DATE_TBA, self::DATE_UNRELEASED];

        $count = 0;

        if (in_array($feedItemGame->upcoming_date_eu, $datesToCheck)) {
            $count++;
        }
        if (in_array($feedItemGame->upcoming_date_us, $datesToCheck)) {
            $count++;
        }
        if (in_array($feedItemGame->upcoming_date_jp, $datesToCheck)) {
            $count++;
        }

        return $count;
    }

    public function countRealDates(FeedItemGame $feedItemGame, DateHandler $dateHandler)
    {
        $count = 0;

        if ($feedItemGame->release_date_eu != null) {
            $count++;
        }
        if ($feedItemGame->release_date_us != null) {
            $count++;
        }
        if ($feedItemGame->release_date_jp != null) {
            $count++;
        }

        return $count;
    }

    public function getSkipText($title)
    {
        $foundSkipText = null;
        foreach ($this->titlesToSkip as $skipText) {
            if (strpos($title, $skipText) !== false) {
                $foundSkipText = $skipText;
                break;
            }
        }
        if ($foundSkipText == null) {
            return null;
        } else {
            return $foundSkipText;
        }
    }
}