<?php

namespace App\Factories;

use App\Developer;

class DeveloperFactory
{
    /**
     * @param $name
     * @param $linkTitle
     * @param null $websiteUrl
     * @param null $twitterId
     * @return Developer
     */
    public static function create($name, $linkTitle, $websiteUrl = null, $twitterId = null)
    {
        return new Developer(
            [
                'name' => $name,
                'link_title' => $linkTitle,
                'website_url' => $websiteUrl,
                'twitter_id' => $twitterId,
            ]
        );
    }
}