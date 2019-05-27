<?php

namespace App\Factories;

use App\Partner;

class GamesCompanyFactory
{
    /**
     * @param int $status
     * @param $name
     * @param $linkTitle
     * @param null $websiteUrl
     * @param null $twitterId
     * @return Partner
     */
    public static function create($status, $name, $linkTitle, $websiteUrl = null, $twitterId = null)
    {
        $typeId = Partner::TYPE_GAMES_COMPANY;

        return new Partner(
            [
                'type_id' => $typeId,
                'status' => $status,
                'name' => $name,
                'link_title' => $linkTitle,
                'website_url' => $websiteUrl,
                'twitter_id' => $twitterId,
            ]
        );
    }
}