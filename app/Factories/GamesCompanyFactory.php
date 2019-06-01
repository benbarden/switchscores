<?php

namespace App\Factories;

use App\Partner;

class GamesCompanyFactory
{
    /**
     * @param $name
     * @param $linkTitle
     * @param null $websiteUrl
     * @param null $twitterId
     * @return Partner
     */
    public static function createActive($name, $linkTitle, $websiteUrl = null, $twitterId = null)
    {
        $typeId = Partner::TYPE_GAMES_COMPANY;
        $status = Partner::STATUS_ACTIVE;

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