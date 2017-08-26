<?php


namespace App\Services;

use App\ActivityFeed;


class ActivityFeedService
{
    public function createNewReview($properties)
    {
        $typeId = ActivityFeed::TYPE_NEW_REVIEW;
        return $this->create($typeId, $properties);
    }

    public function createNewChart($properties)
    {
        $typeId = ActivityFeed::TYPE_NEW_CHART;
        return $this->create($typeId, $properties);
    }

    public function createNewGame($properties)
    {
        $typeId = ActivityFeed::TYPE_NEW_GAME;
        return $this->create($typeId, $properties);
    }

    public function create($activityType, $properties)
    {
        return ActivityFeed::create([
            'activity_type' => $activityType,
            'properties' => $properties,
        ]);
    }

    public function find($id)
    {
        return ActivityFeed::find($id);
    }

    public function getAll($limit = 10)
    {
        $activityFeedItems = ActivityFeed::orderBy('created_at', 'desc')->limit($limit)->get();
        return $activityFeedItems;
    }
}