<?php

namespace App\Domain\ActivityLog;

use App\Models\ActivityLog;

class Repository
{
    public function create(
        $eventType, $userId = null, $eventModel = null, $eventModelId = null, $eventDetails = null
    )
    {
        $activityLog = new ActivityLog(
            [
                'event_type' => $eventType,
                'user_id' => $userId,
                'event_model' => $eventModel,
                'event_model_id' => $eventModelId,
                'event_details' => $eventDetails,
            ]
        );
        $activityLog->save();
        return $activityLog;
    }
}