<?php

namespace App\Domain\Url;

class LinkTitle
{
    public function generate($title)
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

}