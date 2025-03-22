<?php

namespace App\Domain\Url;

class StripUtm
{
    public function clean($url)
    {
        $url = trim($url);

        if (!str_contains($url, '?')) return $url;

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