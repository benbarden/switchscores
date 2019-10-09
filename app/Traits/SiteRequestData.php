<?php

namespace App\Traits;

trait SiteRequestData
{
    /**
     * @return string
     */
    public function getRegionCode()
    {
        return \Request::get('regionCode');
    }
}