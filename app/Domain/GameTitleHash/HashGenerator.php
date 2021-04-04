<?php


namespace App\Domain\GameTitleHash;


class HashGenerator
{
    public function generateHash($title): string
    {
        return md5(strtolower($title));
    }
}