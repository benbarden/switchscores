<?php

namespace App\Traits;

use App\Services\GameCalendarService;
use App\Services\GameImportRuleEshopService;
use App\Services\GameService;
use App\Services\GameTitleHashService;
use App\Services\ReviewLinkService;
use App\Services\ReviewStatsService;

trait SwitchServices
{
    private $services = [];

    // *** Generic get/load/set *** //

    private function getService($key)
    {
        if (!$this->serviceExists($key)) {
            throw new \Exception('Failed to load service with key: '.$key);
        }

        return $this->services[$key];
    }

    private function setService($key, $object)
    {
        $this->services[$key] = $object;
    }

    private function serviceExists($key)
    {
        return array_key_exists($key, $this->services);
    }

    private function loadService($service)
    {
        if ($this->serviceExists($service)) {
            return $this->getService($service);
        }

        $serviceName = $this->getServiceName($service);

        $serviceClass = resolve($serviceName);

        $this->setService($service, $serviceClass);

        return $this->getService($service);
    }

    // ** Map names to classes ** //

    private function getServiceName($serviceKey)
    {
        if (class_exists("App\\Services\\".$serviceKey)) {
            $serviceName = "App\\Services\\".$serviceKey;
        } else {
            throw new \Exception('Failed to load service class: '.$serviceKey);
        }
        return $serviceName;
    }

    // ** Standard service classes ** //

    /**
     * @deprecated
     * @return GameCalendarService
     */
    public function getServiceGameCalendar()
    {
        return $this->loadService('GameCalendarService');
    }

    /**
     * @deprecated
     * @return GameImportRuleEshopService
     */
    public function getServiceGameImportRuleEshop()
    {
        return $this->loadService('GameImportRuleEshopService');
    }

    /**
     * @deprecated
     * @return GameService
     */
    public function getServiceGame()
    {
        return $this->loadService('GameService');
    }

    /**
     * @deprecated
     * @return GameTitleHashService
     */
    public function getServiceGameTitleHash()
    {
        return $this->loadService('GameTitleHashService');
    }

    /**
     * @deprecated
     * @return ReviewLinkService
     */
    public function getServiceReviewLink()
    {
        return $this->loadService('ReviewLinkService');
    }

    /**
     * @deprecated
     * @return ReviewStatsService
     */
    public function getServiceReviewStats()
    {
        return $this->loadService('ReviewStatsService');
    }
}
