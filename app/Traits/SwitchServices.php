<?php

namespace App\Traits;

use App\Services\ViewHelper\Bindings;
use App\Services\ViewHelper\MemberBreadcrumbs;
use App\Services\ViewHelper\StaffBreadcrumbs;

use App\Services\CampaignGameService;
use App\Services\CategoryService;
use App\Services\DataSourceService;
use App\Services\DataSourceIgnoreService;
use App\Services\DataSourceParsedService;
use App\Services\DataSourceRawService;
use App\Services\DbEditGameService;
use App\Services\GameCalendarService;
use App\Services\GameDeveloperService;
use App\Services\GameImportRuleEshopService;
use App\Services\GameImportRuleWikipediaService;
use App\Services\GamePublisherService;
use App\Services\GameRankAllTimeService;
use App\Services\GameRankYearService;
use App\Services\GameRankYearMonthService;
use App\Services\GameReleaseDateService;
use App\Services\GameService;
use App\Services\GameTagService;
use App\Services\GameTitleHashService;
use App\Services\NewsCategoryService;
use App\Services\NewsService;
use App\Services\PartnerFeedLinkService;
use App\Services\PartnerService;
use App\Services\PartnerOutreachService;
use App\Services\QuickReviewService;
use App\Services\ReviewFeedItemService;
use App\Services\ReviewFeedItemTestService;
use App\Services\ReviewLinkService;
use App\Services\ReviewStatsService;
use App\Services\TagService;
use App\Services\TopRatedService;
use App\Services\UrlService;
use App\Services\UserGamesCollectionService;
use App\Services\UserService;

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

    // ** Classes with hierarchy ** //

    /**
     * @deprecated
     * @return Bindings
     */
    public function getServiceViewHelperBindings()
    {
        return $this->loadService("ViewHelper\\Bindings");
    }

    /**
     * @deprecated
     * @return MemberBreadcrumbs
     */
    public function getServiceViewHelperMemberBreadcrumbs()
    {
        return $this->loadService("ViewHelper\\MemberBreadcrumbs");
    }

    /**
     * @deprecated
     * @return StaffBreadcrumbs
     */
    public function getServiceViewHelperStaffBreadcrumbs()
    {
        return $this->loadService("ViewHelper\\StaffBreadcrumbs");
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
     * @return DbEditGameService
     */
    public function getServiceDbEditGame()
    {
        return $this->loadService('DbEditGameService');
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
     * @return GameImportRuleWikipediaService
     */
    public function getServiceGameImportRuleWikipedia()
    {
        return $this->loadService('GameImportRuleWikipediaService');
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
     * @return GameRankAllTimeService
     */
    public function getServiceGameRankAllTime()
    {
        return $this->loadService('GameRankAllTimeService');
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
     * @return GameReleaseDateService
     */
    public function getServiceGameReleaseDate()
    {
        return $this->loadService('GameReleaseDateService');
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
     * @return NewsCategoryService
     */
    public function getServiceNewsCategory()
    {
        return $this->loadService('NewsCategoryService');
    }

    /**
     * @deprecated
     * @return NewsService
     */
    public function getServiceNews()
    {
        return $this->loadService('NewsService');
    }

    /**
     * @deprecated
     * @return PartnerService
     */
    public function getServicePartner()
    {
        return $this->loadService('PartnerService');
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
     * @return PartnerOutreachService
     */
    public function getServicePartnerOutreach()
    {
        return $this->loadService('PartnerOutreachService');
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
     * @return ReviewFeedItemService
     */
    public function getServiceReviewFeedItem()
    {
        return $this->loadService('ReviewFeedItemService');
    }

    /**
     * @deprecated
     * @return ReviewFeedItemTestService
     */
    public function getServiceReviewFeedItemTest()
    {
        return $this->loadService('ReviewFeedItemTestService');
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
     * @return TagService
     */
    public function getServiceTag()
    {
        return $this->loadService('TagService');
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

    /**
     * @deprecated
     * @return UserService
     */
    public function getServiceUser()
    {
        return $this->loadService('UserService');
    }

}
