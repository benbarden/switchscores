<?php

namespace App\Factories;

use App\Models\GamesCompany;
use App\Services\UrlService;

class GamesCompanyFactory
{
    /**
     * @param $name
     * @param $linkTitle
     * @param null $websiteUrl
     * @param null $twitterId
     * $param null $isLowQuality
     * @return GamesCompany
     */
    public static function createActive($name, $linkTitle, $websiteUrl = null, $twitterId = null, $isLowQuality = 0,
                                        $email = null, $threadsId = null, $blueskyId = null)
    {
        return new GamesCompany(
            [
                'name' => $name,
                'link_title' => $linkTitle,
                'website_url' => $websiteUrl,
                'twitter_id' => $twitterId,
                'is_low_quality' => $isLowQuality,
                'email' => $email,
                'threads_id' => $threadsId,
                'bluesky_id' => $blueskyId,
            ]
        );
    }

    public static function createActiveNameOnly($name)
    {
        $serviceUrl = new UrlService();
        $linkText = $serviceUrl->generateLinkText($name);
        return self::createActive($name, $linkText);
    }
}