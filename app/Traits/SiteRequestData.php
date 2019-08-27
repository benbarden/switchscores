<?php

namespace App\Traits;

use App\Services\ServiceContainer;

trait SiteRequestData
{
    /**
     * @return string
     */
    public function getRegionCode()
    {
        return \Request::get('regionCode');
    }

    /**
     * @return ServiceContainer
     */
    public function getServiceContainer()
    {
        return \Request::get('serviceContainer');
    }
}