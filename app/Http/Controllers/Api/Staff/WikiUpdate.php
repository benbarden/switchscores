<?php

namespace App\Http\Controllers\Api\Staff;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class WikiUpdate
{
    use WosServices;
    use SiteRequestData;

    public function updateStatus()
    {
        $serviceFeedItemGame = $this->getServiceFeedItemGame();

        $request = request();

        $feedItemGameId = $request->itemId;
        $statusId = $request->newStatusId;

        if (!$feedItemGameId) {
            return response()->json(['error' => 'Missing data: itemId'], 400);
        }
        if (!$statusId) {
            return response()->json(['error' => 'Missing data: newStatusId'], 400);
        }

        // Validation
        $feedItemGame = $serviceFeedItemGame->find($feedItemGameId);
        if (!$feedItemGame) {
            return response()->json(['error' => 'Record not found: '.$feedItemGameId], 400);
        }

        if ($feedItemGame->status_id == $statusId) {
            return response()->json(['error' => 'Status hasn\'t changed'], 400);
        }

        // All OK - update record
        $serviceFeedItemGame->updateStatus($feedItemGame, $statusId);

        return response()->json(['message' => 'Success'], 200);
    }
}
