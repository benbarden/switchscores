<?php

namespace App\Traits;

use App\Services\StaffDashboards\CategorisationService;

use App\Services\ActivityFeedService;
use App\Services\AuditService;
use App\Services\CrawlerWikipediaGamesListSourceService;
use App\Services\EshopEuropeAlertService;
use App\Services\EshopEuropeGameService;
use App\Services\FeedItemGameService;
use App\Services\FeedItemReviewService;
use App\Services\GameActionListService;
use App\Services\GameCalendarService;
use App\Services\GameDeveloperService;
use App\Services\GameFilterListService;
use App\Services\GameGenreService;
use App\Services\GameImportRuleEshopService;
use App\Services\GameImportRuleWikipediaService;
use App\Services\GamePrimaryTypeService;
use App\Services\GamePublisherService;
use App\Services\GameRankAllTimeService;
use App\Services\GameRankYearService;
use App\Services\GameRankYearMonthService;
use App\Services\GameReleaseDateService;
use App\Services\GameSeriesService;
use App\Services\GameService;
use App\Services\GameTagService;
use App\Services\GameTitleHashService;
use App\Services\GenreService;
use App\Services\NewsCategoryService;
use App\Services\NewsService;
use App\Services\PartnerService;
use App\Services\PartnerReviewService;
use App\Services\QuickReviewService;
use App\Services\ReviewLinkService;
use App\Services\ReviewStatsService;
use App\Services\SiteAlertService;
use App\Services\TagService;
use App\Services\TopRatedService;
use App\Services\UrlService;
use App\Services\UserGamesCollectionService;
use App\Services\UserService;

trait WosServices
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

    // ** Get specific classes ** //

    /**
     * @return ActivityFeedService
     */
    public function getServiceActivityFeed()
    {
        return $this->loadService('ActivityFeedService');
    }

    /**
     * @return AuditService
     */
    public function getServiceAudit()
    {
        return $this->loadService('AuditService');
    }

    /**
     * @return CategorisationService
     */
    public function getServiceStaffDashboardsCategorisation()
    {
        return $this->loadService("StaffDashboards\\CategorisationService");
    }

    /**
     * @return CrawlerWikipediaGamesListSourceService
     */
    public function getServiceCrawlerWikipediaGamesListSource()
    {
        return $this->loadService('CrawlerWikipediaGamesListSourceService');
    }

    /**
     * @return EshopEuropeGameService
     */
    public function getServiceEshopEuropeGame()
    {
        return $this->loadService('EshopEuropeGameService');
    }

    /**
     * @return EshopEuropeAlertService
     */
    public function getServiceEshopEuropeAlert()
    {
        return $this->loadService('EshopEuropeAlertService');
    }

    /**
     * @return FeedItemGameService
     */
    public function getServiceFeedItemGame()
    {
        return $this->loadService('FeedItemGameService');
    }

    /**
     * @return FeedItemReviewService
     */
    public function getServiceFeedItemReview()
    {
        return $this->loadService('FeedItemReviewService');
    }

    /**
     * @return GameCalendarService
     */
    public function getServiceGameCalendar()
    {
        return $this->loadService('GameCalendarService');
    }

    /**
     * @return GameDeveloperService
     */
    public function getServiceGameDeveloper()
    {
        return $this->loadService('GameDeveloperService');
    }

    /**
     * @return GameGenreService
     */
    public function getServiceGameGenre()
    {
        return $this->loadService('GameGenreService');
    }

    /**
     * @return GameActionListService
     */
    public function getServiceGameActionList()
    {
        return $this->loadService('GameActionListService');
    }

    /**
     * @return GameFilterListService
     */
    public function getServiceGameFilterList()
    {
        return $this->loadService('GameFilterListService');
    }

    /**
     * @return GameImportRuleEshopService
     */
    public function getServiceGameImportRuleEshop()
    {
        return $this->loadService('GameImportRuleEshopService');
    }

    /**
     * @return GameImportRuleWikipediaService
     */
    public function getServiceGameImportRuleWikipedia()
    {
        return $this->loadService('GameImportRuleWikipediaService');
    }

    /**
     * @return GamePrimaryTypeService
     */
    public function getServiceGamePrimaryType()
    {
        return $this->loadService('GamePrimaryTypeService');
    }

    /**
     * @return GamePublisherService
     */
    public function getServiceGamePublisher()
    {
        return $this->loadService('GamePublisherService');
    }

    /**
     * @return GameRankAllTimeService
     */
    public function getServiceGameRankAllTime()
    {
        return $this->loadService('GameRankAllTimeService');
    }

    /**
     * @return GameRankYearService
     */
    public function getServiceGameRankYear()
    {
        return $this->loadService('GameRankYearService');
    }

    /**
     * @return GameRankYearMonthService
     */
    public function getServiceGameRankYearMonth()
    {
        return $this->loadService('GameRankYearMonthService');
    }

    /**
     * @return GameReleaseDateService
     */
    public function getServiceGameReleaseDate()
    {
        return $this->loadService('GameReleaseDateService');
    }

    /**
     * @return GameSeriesService
     */
    public function getServiceGameSeries()
    {
        return $this->loadService('GameSeriesService');
    }

    /**
     * @return GameService
     */
    public function getServiceGame()
    {
        return $this->loadService('GameService');
    }

    /**
     * @return GameTagService
     */
    public function getServiceGameTag()
    {
        return $this->loadService('GameTagService');
    }

    /**
     * @return GameTitleHashService
     */
    public function getServiceGameTitleHash()
    {
        return $this->loadService('GameTitleHashService');
    }

    /**
     * @return GenreService
     */
    public function getServiceGenre()
    {
        return $this->loadService('GenreService');
    }

    /**
     * @return NewsCategoryService
     */
    public function getServiceNewsCategory()
    {
        return $this->loadService('NewsCategoryService');
    }

    /**
     * @return NewsService
     */
    public function getServiceNews()
    {
        return $this->loadService('NewsService');
    }

    /**
     * @return PartnerService
     */
    public function getServicePartner()
    {
        return $this->loadService('PartnerService');
    }

    /**
     * @return PartnerReviewService
     */
    public function getServicePartnerReview()
    {
        return $this->loadService('PartnerReviewService');
    }

    /**
     * @return QuickReviewService
     */
    public function getServiceQuickReview()
    {
        return $this->loadService('QuickReviewService');
    }

    /**
     * @return ReviewLinkService
     */
    public function getServiceReviewLink()
    {
        return $this->loadService('ReviewLinkService');
    }

    /**
     * @return ReviewStatsService
     */
    public function getServiceReviewStats()
    {
        return $this->loadService('ReviewStatsService');
    }

    /**
     * @return SiteAlertService
     */
    public function getServiceSiteAlert()
    {
        return $this->loadService('SiteAlertService');
    }

    /**
     * @return TagService
     */
    public function getServiceTag()
    {
        return $this->loadService('TagService');
    }

    /**
     * @return TopRatedService
     */
    public function getServiceTopRated()
    {
        return $this->loadService('TopRatedService');
    }

    /**
     * @return UrlService
     */
    public function getServiceUrl()
    {
        return $this->loadService('UrlService');
    }

    /**
     * @return UserGamesCollectionService
     */
    public function getServiceUserGamesCollection()
    {
        return $this->loadService('UserGamesCollectionService');
    }

    /**
     * @return UserService
     */
    public function getServiceUser()
    {
        return $this->loadService('UserService');
    }

}
