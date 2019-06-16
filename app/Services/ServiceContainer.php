<?php


namespace App\Services;


class ServiceContainer
{
    const KEY_ACTIVITY_FEED_SERVICE = 'ActivityFeedService';
    const KEY_CHARTS_DATE_SERVICE = 'ChartsDateService';
    const KEY_CHARTS_RANKING_GLOBAL_SERVICE = 'ChartsRankingGlobalService';
    const KEY_CRAWLER_WIKIPEDIA_GAMES_LIST_SOURCE_SERVICE = 'CrawlerWikipediaGamesListSourceService';
    const KEY_DEVELOPER_SERVICE = 'DeveloperService';
    const KEY_ESHOP_EUROPE_GAME_SERVICE = 'EshopEuropeGameService';
    const KEY_FEED_ITEM_GAME_SERVICE = 'FeedItemGameService';
    const KEY_FEED_ITEM_REVIEW_SERVICE = 'FeedItemReviewService';
    const KEY_GAME_CALENDAR_SERVICE = 'GameCalendarService';
    const KEY_GAME_CHANGE_HISTORY_SERVICE = 'GameChangeHistoryService';
    const KEY_GAME_DEVELOPER_SERVICE = 'GameDeveloperService';
    const KEY_GAME_GENRE_SERVICE = 'GameGenreService';
    const KEY_GAME_PUBLISHER_SERVICE = 'GamePublisherService';
    const KEY_GAME_RANK_ALLTIME_SERVICE = 'GameRankAllTimeService';
    const KEY_GAME_RANK_UPDATE_SERVICE = 'GameRankUpdateService';
    const KEY_GAME_RANK_YEAR_SERVICE = 'GameRankYearService';
    const KEY_GAME_RANK_YEARMONTH_SERVICE = 'GameRankYearMonthService';
    const KEY_GAME_RELEASE_DATE_SERVICE = 'GameReleaseDateService';
    const KEY_GAME_SERVICE = 'GameService';
    const KEY_GAME_TAG_SERVICE = 'GameTagService';
    const KEY_GAME_TITLE_HASH_SERVICE = 'GameTitleHashService';
    const KEY_GENRE_SERVICE = 'GenreService';
    const KEY_NEWS_CATEGORY_SERVICE = 'NewsCategoryService';
    const KEY_NEWS_SERVICE = 'NewsService';
    const KEY_PARTNER_SERVICE = 'PartnerService';
    const KEY_PARTNER_REVIEW_SERVICE = 'PartnerReviewService';
    const KEY_PUBLISHER_SERVICE = 'PublisherService';
    const KEY_REVIEW_LINK_SERVICE = 'ReviewLinkService';
    const KEY_REVIEW_QUICK_RATING_SERVICE = 'ReviewQuickRatingService';
    const KEY_REVIEW_STATS_SERVICE = 'ReviewStatsService';
    const KEY_REVIEW_USER_SERVICE = 'ReviewUserService';
    const KEY_SITE_ALERT_SERVICE = 'SiteAlertService';
    const KEY_TAG_SERVICE = 'TagService';
    const KEY_TOP_RATED_SERVICE = 'TopRatedService';
    const KEY_URL_SERVICE = 'UrlService';
    const KEY_USER_GAMES_COLLECTION_SERVICE = 'UserGamesCollectionService';
    const KEY_USER_LIST_SERVICE = 'UserListService';
    const KEY_USER_LIST_ITEM_SERVICE = 'UserListItemService';
    const KEY_USER_SERVICE = 'UserService';

    private $services = [];

    // *** Generic get/load/set *** //

    private function get($key)
    {
        if (!$this->exists($key)) {
            throw new \Exception('Failed to load service with key: '.$key);
        }

        return $this->services[$key];
    }

    private function set($key, $object)
    {
        $this->services[$key] = $object;
    }

    private function exists($key)
    {
        return array_key_exists($key, $this->services);
    }

    private function load($service)
    {
        if ($this->exists($service)) {
            return $this->get($service);
        }

        $serviceName = $this->getServiceName($service);

        $serviceClass = resolve($serviceName);

        $this->set($service, $serviceClass);

        return $this->get($service);
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
    public function getActivityFeedService()
    {
        return $this->load(self::KEY_ACTIVITY_FEED_SERVICE);
    }

    /**
     * @return ChartsDateService
     */
    public function getChartsDateService()
    {
        return $this->load(self::KEY_CHARTS_DATE_SERVICE);
    }

    /**
     * @return ChartsRankingGlobalService
     */
    public function getChartsRankingGlobalService()
    {
        return $this->load(self::KEY_CHARTS_RANKING_GLOBAL_SERVICE);
    }

    /**
     * @return CrawlerWikipediaGamesListSourceService
     */
    public function getCrawlerWikipediaGamesListSourceService()
    {
        return $this->load(self::KEY_CRAWLER_WIKIPEDIA_GAMES_LIST_SOURCE_SERVICE);
    }

    /**
     * @return DeveloperService
     */
    public function getDeveloperService()
    {
        return $this->load(self::KEY_DEVELOPER_SERVICE);
    }

    /**
     * @return EshopEuropeGameService
     */
    public function getEshopEuropeGameService()
    {
        return $this->load(self::KEY_ESHOP_EUROPE_GAME_SERVICE);
    }

    /**
     * @return FeedItemGameService
     */
    public function getFeedItemGameService()
    {
        return $this->load(self::KEY_FEED_ITEM_GAME_SERVICE);
    }

    /**
     * @return FeedItemReviewService
     */
    public function getFeedItemReviewService()
    {
        return $this->load(self::KEY_FEED_ITEM_REVIEW_SERVICE);
    }

    /**
     * @return GameCalendarService
     */
    public function getGameCalendarService()
    {
        return $this->load(self::KEY_GAME_CALENDAR_SERVICE);
    }

    /**
     * @return GameChangeHistoryService
     */
    public function getGameChangeHistoryService()
    {
        return $this->load(self::KEY_GAME_CHANGE_HISTORY_SERVICE);
    }

    /**
     * @return GameDeveloperService
     */
    public function getGameDeveloperService()
    {
        return $this->load(self::KEY_GAME_DEVELOPER_SERVICE);
    }

    /**
     * @return GameGenreService
     */
    public function getGameGenreService()
    {
        return $this->load(self::KEY_GAME_GENRE_SERVICE);
    }

    /**
     * @return GamePublisherService
     */
    public function getGamePublisherService()
    {
        return $this->load(self::KEY_GAME_PUBLISHER_SERVICE);
    }

    /**
     * @return GameRankAllTimeService
     */
    public function getGameRankAllTimeService()
    {
        return $this->load(self::KEY_GAME_RANK_ALLTIME_SERVICE);
    }

    /**
     * @return GameRankUpdateService
     */
    public function getGameRankUpdateService()
    {
        return $this->load(self::KEY_GAME_RANK_UPDATE_SERVICE);
    }

    /**
     * @return GameRankYearService
     */
    public function getGameRankYearService()
    {
        return $this->load(self::KEY_GAME_RANK_YEAR_SERVICE);
    }

    /**
     * @return GameRankYearMonthService
     */
    public function getGameRankYearMonthService()
    {
        return $this->load(self::KEY_GAME_RANK_YEARMONTH_SERVICE);
    }

    /**
     * @return GameReleaseDateService
     */
    public function getGameReleaseDateService()
    {
        return $this->load(self::KEY_GAME_RELEASE_DATE_SERVICE);
    }

    /**
     * @return GameService
     */
    public function getGameService()
    {
        return $this->load(self::KEY_GAME_SERVICE);
    }

    /**
     * @return GameTagService
     */
    public function getGameTagService()
    {
        return $this->load(self::KEY_GAME_TAG_SERVICE);
    }

    /**
     * @return GameTitleHashService
     */
    public function getGameTitleHashService()
    {
        return $this->load(self::KEY_GAME_TITLE_HASH_SERVICE);
    }

    /**
     * @return GenreService
     */
    public function getGenreService()
    {
        return $this->load(self::KEY_GENRE_SERVICE);
    }

    /**
     * @return NewsCategoryService
     */
    public function getNewsCategoryService()
    {
        return $this->load(self::KEY_NEWS_CATEGORY_SERVICE);
    }

    /**
     * @return NewsService
     */
    public function getNewsService()
    {
        return $this->load(self::KEY_NEWS_SERVICE);
    }

    /**
     * @return PartnerService
     */
    public function getPartnerService()
    {
        return $this->load(self::KEY_PARTNER_SERVICE);
    }

    /**
     * @return PartnerReviewService
     */
    public function getPartnerReviewService()
    {
        return $this->load(self::KEY_PARTNER_REVIEW_SERVICE);
    }

    /**
     * @return PublisherService
     */
    public function getPublisherService()
    {
        return $this->load(self::KEY_PUBLISHER_SERVICE);
    }

    /**
     * @return ReviewLinkService
     */
    public function getReviewLinkService()
    {
        return $this->load(self::KEY_REVIEW_LINK_SERVICE);
    }

    /**
     * @return ReviewQuickRatingService
     */
    public function getReviewQuickRatingService()
    {
        return $this->load(self::KEY_REVIEW_QUICK_RATING_SERVICE);
    }

    /**
     * @return ReviewStatsService
     */
    public function getReviewStatsService()
    {
        return $this->load(self::KEY_REVIEW_STATS_SERVICE);
    }

    /**
     * @return ReviewUserService
     */
    public function getReviewUserService()
    {
        return $this->load(self::KEY_REVIEW_USER_SERVICE);
    }

    /**
     * @return SiteAlertService
     */
    public function getSiteAlertService()
    {
        return $this->load(self::KEY_SITE_ALERT_SERVICE);
    }

    /**
     * @return TagService
     */
    public function getTagService()
    {
        return $this->load(self::KEY_TAG_SERVICE);
    }

    /**
     * @return TopRatedService
     */
    public function getTopRatedService()
    {
        return $this->load(self::KEY_TOP_RATED_SERVICE);
    }

    /**
     * @return UrlService
     */
    public function getUrlService()
    {
        return $this->load(self::KEY_URL_SERVICE);
    }

    /**
     * @return UserGamesCollectionService
     */
    public function getUserGamesCollectionService()
    {
        return $this->load(self::KEY_USER_GAMES_COLLECTION_SERVICE);
    }

    /**
     * @return UserListItemService
     */
    public function getUserListItemService()
    {
        return $this->load(self::KEY_USER_LIST_ITEM_SERVICE);
    }

    /**
     * @return UserListService
     */
    public function getUserListService()
    {
        return $this->load(self::KEY_USER_LIST_SERVICE);
    }

    /**
     * @return UserService
     */
    public function getUserService()
    {
        return $this->load(self::KEY_USER_SERVICE);
    }
}