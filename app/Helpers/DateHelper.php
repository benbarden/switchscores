<?php


namespace App\Helpers;


class DateHelper
{
    static function isNew($releaseDate)
    {
        if (date('Y-m-d', strtotime('-7 days')) < $releaseDate) {
            return true;
        } else {
            return false;
        }
    }
}