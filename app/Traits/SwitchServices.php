<?php

namespace App\Traits;

use App\Services\CampaignGameService;
use App\Services\CategoryService;
use App\Services\DataSourceService;
use App\Services\DataSourceIgnoreService;
use App\Services\DataSourceParsedService;
use App\Services\DataSourceRawService;
use App\Services\GameCalendarService;
use App\Services\GameDeveloperService;
use App\Services\GameImportRuleEshopService;
use App\Services\GamePublisherService;
use App\Services\GameRankYearService;
use App\Services\GameRankYearMonthService;
use App\Services\GameService;
use App\Services\GameTagService;
use App\Services\GameTitleHashService;
use App\Services\PartnerFeedLinkService;
use App\Services\QuickReviewService;
use App\Services\ReviewLinkService;
use App\Services\ReviewStatsService;
use App\Services\TopRatedService;
use App\Services\UrlService;
use App\Services\UserGamesCollectionService;

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
     * @return CampaignGameService
     */
    public function getServiceCampaignGame()
    {
        return $this->loadService('CampaignGameService');
    }

    /**
     * @deprecated
     * @return CategoryService
     */
    public function getServiceCategory()
    {
        return $this->loadService('CategoryService');
    }

    /**
     * @deprecated
     * @return DataSourceService
     */
    public function getServiceDataSource()
    {
        return $this->loadService('DataSourceService');
    }

    /**
     * @deprecated
     * @return DataSourceIgnoreService
     */
    public function getServiceDataSourceIgnore()
    {
        return $this->loadService('DataSourceIgnoreService');
    }

    /**
     * @deprecated
     * @return DataSourceParsedService
     */
    public function getServiceDataSourceParsed()
    {
        return $this->loadService('DataSourceParsedService');
    }

    /**
     * @deprecated
     * @return DataSourceRawService
     */
    public function getServiceDataSourceRaw()
    {
        return $this->loadService('DataSourceRawService');
    }

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
     * @return GameDeveloperService
     */
    public function getServiceGameDeveloper()
    {
        return $this->loadService('GameDeveloperService');
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
     * @return GamePublisherService
     */
    public function getServiceGamePublisher()
    {
        return $this->loadService('GamePublisherService');
    }

    /**
     * @deprecated
     * @return GameRankYearService
     */
    public function getServiceGameRankYear()
    {
        return $this->loadService('GameRankYearService');
    }

    /**
     * @deprecated
     * @return GameRankYearMonthService
     */
    public function getServiceGameRankYearMonth()
    {
        return $this->loadService('GameRankYearMonthService');
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
     * @return GameTagService
     */
    public function getServiceGameTag()
    {
        return $this->loadService('GameTagService');
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
     * @return PartnerFeedLinkService
     */
    public function getServicePartnerFeedLink()
    {
        return $this->loadService('PartnerFeedLinkService');
    }

    /**
     * @deprecated
     * @return QuickReviewService
     */
    public function getServiceQuickReview()
    {
        return $this->loadService('QuickReviewService');
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

    /**
     * @deprecated
     * @return TopRatedService
     */
    public function getServiceTopRated()
    {
        return $this->loadService('TopRatedService');
    }

    /**
     * @deprecated
     * @return UrlService
     */
    public function getServiceUrl()
    {
        return $this->loadService('UrlService');
    }

    /**
     * @deprecated
     * @return UserGamesCollectionService
     */
    public function getServiceUserGamesCollection()
    {
        return $this->loadService('UserGamesCollectionService');
    }
}
