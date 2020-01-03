<?php


namespace App\Factories\Tests;

use App\FeedItemGame;

class FeedItemGameFactory
{
    public static function makeSimpleForNoChange()
    {
        $newFeedItem = new FeedItemGame();
        $newFeedItem->item_developers = 'Developer 1';
        $newFeedItem->item_publishers = 'Publisher 1';
        $newFeedItem->release_date_eu = '2017-03-03';
        $newFeedItem->upcoming_date_eu = '2017-03-03';
        $newFeedItem->is_released_eu = '1';
        return $newFeedItem;
    }

    public static function makeFullWithDifferences()
    {
        $newFeedItem = new FeedItemGame();
        $newFeedItem->item_developers = 'DEF Developer';
        $newFeedItem->item_publishers = 'DEF Publisher';
        $newFeedItem->release_date_eu = '2018-01-01';
        $newFeedItem->upcoming_date_eu = '2018-01-01';
        $newFeedItem->is_released_eu = '1';
        $newFeedItem->release_date_us = '2018-02-02';
        $newFeedItem->upcoming_date_us = '2018-02-02';
        $newFeedItem->is_released_us = '1';
        $newFeedItem->release_date_jp = '2018-03-03';
        $newFeedItem->upcoming_date_jp = '2018-03-03';
        $newFeedItem->is_released_jp = '1';
        return $newFeedItem;
    }
}