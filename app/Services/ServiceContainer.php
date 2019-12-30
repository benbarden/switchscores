<?php


namespace App\Services;


class ServiceContainer
{
    const KEY_CRAWLER_WIKIPEDIA_GAMES_LIST_SOURCE_SERVICE = 'CrawlerWikipediaGamesListSourceService';
    const KEY_ESHOP_EUROPE_GAME_SERVICE = 'EshopEuropeGameService';
    const KEY_FEED_ITEM_GAME_SERVICE = 'FeedItemGameService';
    const KEY_FEED_ITEM_REVIEW_SERVICE = 'FeedItemReviewService';
    const KEY_GAME_CALENDAR_SERVICE = 'GameCalendarService';
    const KEY_GAME_DEVELOPER_SERVICE = 'GameDeveloperService';
    const KEY_GAME_GENRE_SERVICE = 'GameGenreService';
    const KEY_GAME_ACTION_LIST_SERVICE = 'GameActionListService';
    const KEY_GAME_FILTER_LIST_SERVICE = 'GameFilterListService';
    const KEY_GAME_PRIMARY_TYPE_SERVICE = 'GamePrimaryTypeService';
    const KEY_GAME_PUBLISHER_SERVICE = 'GamePublisherService';
    const KEY_GAME_RANK_ALLTIME_SERVICE = 'GameRankAllTimeService';
    const KEY_GAME_RANK_YEAR_SERVICE = 'GameRankYearService';
    const KEY_GAME_RANK_YEARMONTH_SERVICE = 'GameRankYearMonthService';
    const KEY_GAME_RELEASE_DATE_SERVICE = 'GameReleaseDateService';
    const KEY_GAME_SERIES_SERVICE = 'GameSeriesService';
    const KEY_GAME_SERVICE = 'GameService';
    const KEY_GAME_TAG_SERVICE = 'GameTagService';
    const KEY_GAME_TITLE_HASH_SERVICE = 'GameTitleHashService';
    const KEY_GENRE_SERVICE = 'GenreService';
    const KEY_NEWS_CATEGORY_SERVICE = 'NewsCategoryService';
    const KEY_NEWS_SERVICE = 'NewsService';
    const KEY_PARTNER_SERVICE = 'PartnerService';
    const KEY_PARTNER_REVIEW_SERVICE = 'PartnerReviewService';
    const KEY_REVIEW_LINK_SERVICE = 'ReviewLinkService';
    const KEY_REVIEW_QUICK_RATING_SERVICE = 'ReviewQuickRatingService';
    const KEY_REVIEW_STATS_SERVICE = 'ReviewStatsService';
    const KEY_REVIEW_USER_SERVICE = 'ReviewUserService';
    const KEY_SITE_ALERT_SERVICE = 'SiteAlertService';
    const KEY_TAG_SERVICE = 'TagService';
    const KEY_TOP_RATED_SERVICE = 'TopRatedService';
    const KEY_URL_SERVICE = 'UrlService';
    const KEY_USER_GAMES_COLLECTION_SERVICE = 'UserGamesCollectionService';
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
     * @deprecated
     * @return EshopEuropeGameService
     */
    public function getEshopEuropeGameService()
    {
        return $this->load(self::KEY_ESHOP_EUROPE_GAME_SERVICE);
    }

    /**
     * @deprecated
     * @return FeedItemGameService
     */
    public function getFeedItemGameService()
    {
        return $this->load(self::KEY_FEED_ITEM_GAME_SERVICE);
    }

    /**
     * @deprecated
     * @return FeedItemReviewService
     */
    public function getFeedItemReviewService()
    {
        return $this->load(self::KEY_FEED_ITEM_REVIEW_SERVICE);
    }

    /**
     * @deprecated
     * @return GameCalendarService
     */
    public function getGameCalendarService()
    {
        return $this->load(self::KEY_GAME_CALENDAR_SERVICE);
    }

    /**
     * @deprecated
     * @return GameDeveloperService
     */
    public function getGameDeveloperService()
    {
        return $this->load(self::KEY_GAME_DEVELOPER_SERVICE);
    }

    /**
     * @deprecated
     * @return GameGenreService
     */
    public function getGameGenreService()
    {
        return $this->load(self::KEY_GAME_GENRE_SERVICE);
    }

    /**
     * @deprecated
     * @return GameActionListService
     */
    public function getGameActionListService()
    {
        return $this->load(self::KEY_GAME_ACTION_LIST_SERVICE);
    }

    /**
     * @deprecated
     * @return GameFilterListService
     */
    public function getGameFilterListService()
    {
        return $this->load(self::KEY_GAME_FILTER_LIST_SERVICE);
    }

    /**
     * @deprecated
     * @return GamePrimaryTypeService
     */
    public function getGamePrimaryTypeService()
    {
        return $this->load(self::KEY_GAME_PRIMARY_TYPE_SERVICE);
    }

    /**
     * @deprecated
     * @return GamePublisherService
     */
    public function getGamePublisherService()
    {
        return $this->load(self::KEY_GAME_PUBLISHER_SERVICE);
    }

    /**
     * @deprecated
     * @return GameRankAllTimeService
     */
    public function getGameRankAllTimeService()
    {
        return $this->load(self::KEY_GAME_RANK_ALLTIME_SERVICE);
    }

    /**
     * @deprecated
     * @return GameRankYearService
     */
    public function getGameRankYearService()
    {
        return $this->load(self::KEY_GAME_RANK_YEAR_SERVICE);
    }

    /**
     * @deprecated
     * @return GameRankYearMonthService
     */
    public function getGameRankYearMonthService()
    {
        return $this->load(self::KEY_GAME_RANK_YEARMONTH_SERVICE);
    }

    /**
     * @deprecated
     * @return GameReleaseDateService
     */
    public function getGameReleaseDateService()
    {
        return $this->load(self::KEY_GAME_RELEASE_DATE_SERVICE);
    }

    /**
     * @deprecated
     * @return GameSeriesService
     */
    public function getGameSeriesService()
    {
        return $this->load(self::KEY_GAME_SERIES_SERVICE);
    }

    /**
     * @deprecated
     * @return GameService
     */
    public function getGameService()
    {
        return $this->load(self::KEY_GAME_SERVICE);
    }

    /**
     * @deprecated
     * @return GameTagService
     */
    public function getGameTagService()
    {
        return $this->load(self::KEY_GAME_TAG_SERVICE);
    }

    /**
     * @deprecated
     * @return GameTitleHashService
     */
    public function getGameTitleHashService()
    {
        return $this->load(self::KEY_GAME_TITLE_HASH_SERVICE);
    }

    /**
     * @deprecated
     * @return GenreService
     */
    public function getGenreService()
    {
        return $this->load(self::KEY_GENRE_SERVICE);
    }

    /**
     * @deprecated
     * @return NewsCategoryService
     */
    public function getNewsCategoryService()
    {
        return $this->load(self::KEY_NEWS_CATEGORY_SERVICE);
    }

    /**
     * @deprecated
     * @return NewsService
     */
    public function getNewsService()
    {
        return $this->load(self::KEY_NEWS_SERVICE);
    }

    /**
     * @deprecated
     * @return PartnerService
     */
    public function getPartnerService()
    {
        return $this->load(self::KEY_PARTNER_SERVICE);
    }

    /**
     * @deprecated
     * @return PartnerReviewService
     */
    public function getPartnerReviewService()
    {
        return $this->load(self::KEY_PARTNER_REVIEW_SERVICE);
    }

    /**
     * @deprecated
     * @return ReviewLinkService
     */
    public function getReviewLinkService()
    {
        return $this->load(self::KEY_REVIEW_LINK_SERVICE);
    }

    /**
     * @deprecated
     * @return ReviewQuickRatingService
     */
    public function getReviewQuickRatingService()
    {
        return $this->load(self::KEY_REVIEW_QUICK_RATING_SERVICE);
    }

    /**
     * @deprecated
     * @return ReviewStatsService
     */
    public function getReviewStatsService()
    {
        return $this->load(self::KEY_REVIEW_STATS_SERVICE);
    }

    /**
     * @deprecated
     * @return ReviewUserService
     */
    public function getReviewUserService()
    {
        return $this->load(self::KEY_REVIEW_USER_SERVICE);
    }

    /**
     * @deprecated
     * @return SiteAlertService
     */
    public function getSiteAlertService()
    {
        return $this->load(self::KEY_SITE_ALERT_SERVICE);
    }

    /**
     * @deprecated
     * @return TagService
     */
    public function getTagService()
    {
        return $this->load(self::KEY_TAG_SERVICE);
    }

    /**
     * @deprecated
     * @return TopRatedService
     */
    public function getTopRatedService()
    {
        return $this->load(self::KEY_TOP_RATED_SERVICE);
    }

    /**
     * @deprecated
     * @return UrlService
     */
    public function getUrlService()
    {
        return $this->load(self::KEY_URL_SERVICE);
    }

    /**
     * @deprecated
     * @return UserGamesCollectionService
     */
    public function getUserGamesCollectionService()
    {
        return $this->load(self::KEY_USER_GAMES_COLLECTION_SERVICE);
    }

    /**
     * @deprecated
     * @return UserService
     */
    public function getUserService()
    {
        return $this->load(self::KEY_USER_SERVICE);
    }
}