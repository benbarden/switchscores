<?php


namespace App\Services;

use App\Services\GameService;


class ServiceContainer
{
    const KEY_GAME_SERVICE = 'GameService';
    const KEY_GAME_RELEASE_DATE_SERVICE = 'GameReleaseDateService';
    const KEY_GAME_TITLE_HASH_SERVICE = 'GameTitleHashService';
    const KEY_FEED_ITEM_GAME_SERVICE = 'FeedItemGameService';
    const KEY_FEED_ITEM_REVIEW_SERVICE = 'FeedItemReviewService';
    const KEY_REVIEW_SITE_SERVICE = 'ReviewSiteService';

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
        switch ($serviceKey) {
            case self::KEY_GAME_SERVICE:
            case self::KEY_GAME_RELEASE_DATE_SERVICE:
            case self::KEY_GAME_TITLE_HASH_SERVICE:
            case self::KEY_FEED_ITEM_GAME_SERVICE:
            case self::KEY_FEED_ITEM_REVIEW_SERVICE:
            case self::KEY_REVIEW_SITE_SERVICE:
                $serviceName = "Services\\".$serviceKey;
                break;
            default:
                throw new \Exception('Failed to load service class: '.$serviceKey);
                break;
        }
        return $serviceName;
    }

    // ** Get specific classes ** //

    /**
     * @return FeedItemGameService
     */
    public function getFeedItemGameService()
    {
        $serviceKey = self::KEY_FEED_ITEM_GAME_SERVICE;

        $serviceObject = $this->load($serviceKey);

        return $serviceObject;
    }

    /**
     * @return FeedItemReviewService
     */
    public function getFeedItemReviewService()
    {
        $serviceKey = self::KEY_FEED_ITEM_REVIEW_SERVICE;

        $serviceObject = $this->load($serviceKey);

        return $serviceObject;
    }

    /**
     * @return GameService
     */
    public function getGameService()
    {
        $serviceKey = self::KEY_GAME_SERVICE;

        $serviceObject = $this->load($serviceKey);

        return $serviceObject;
    }

    /**
     * @return GameReleaseDateService
     */
    public function getGameReleaseDateService()
    {
        $serviceKey = self::KEY_GAME_RELEASE_DATE_SERVICE;

        $serviceObject = $this->load($serviceKey);

        return $serviceObject;
    }

    /**
     * @return GameTitleHashService
     */
    public function getGameTitleHashService()
    {
        $serviceKey = self::KEY_GAME_TITLE_HASH_SERVICE;

        $serviceObject = $this->load($serviceKey);

        return $serviceObject;
    }

    /**
     * @return ReviewSiteService
     */
    public function getReviewSiteService()
    {
        $serviceKey = self::KEY_REVIEW_SITE_SERVICE;

        $serviceObject = $this->load($serviceKey);

        return $serviceObject;
    }
}