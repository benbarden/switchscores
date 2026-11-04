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

    public function getAll(?string $eventType = null)
    {
        $query = ActivityLog::with('user', 'game')->orderBy('id', 'desc');

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        return $query->get();
    }
}