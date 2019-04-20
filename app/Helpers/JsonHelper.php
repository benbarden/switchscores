<?php


namespace App\Helpers;


class JsonHelper
{
    static function jsonDecode($data)
    {
        return json_decode($data, true);
    }
}