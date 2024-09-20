<?php


namespace App\Services;


class UrlService
{
    /**
     * @deprecated
     */
    public function generateLinkText($title)
    {
        $linkText = $title;

        $linkText = strip_tags($linkText);
        $linkText = html_entity_decode($linkText);
        $linkText = urldecode($linkText);
        $linkText = str_replace("'", '', $linkText);
        $linkText = preg_replace('/[^A-Za-z0-9]/', ' ', $linkText);
        // Replace multiple spaces with single space
        $linkText = preg_replace('/ +/', ' ', $linkText);
        $linkText = trim($linkText);
        $linkText = strtolower($linkText);
        $linkText = str_replace(' ', '-', $linkText);
        $linkText = str_replace('_', '-', $linkText);

        return $linkText;
    }

    public function cleanReviewFeedUrl($url)
    {
        if (strpos($url, '?') === false) return $url;

        $baseUrl = explode('?', $url);

        $parsed = parse_url($url);
        $query = $parsed['query'];

        parse_str($query, $params);

        unset($params['utm_source']);
        unset($params['utm_medium']);
        unset($params['utm_campaign']);
        $cleanQueryString = http_build_query($params);

        if ($cleanQueryString) {
            $cleanQueryString = '?'.$cleanQueryString;
        }

        $fullUrl = $baseUrl[0].$cleanQueryString;

        return $fullUrl;
    }
}