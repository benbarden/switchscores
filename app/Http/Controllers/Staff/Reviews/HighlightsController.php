<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class HighlightsController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsReviewsSubpage('Review highlights');

        $bindings['HighlightsRecentlyRanked'] = $this->getServiceReviewLink()->getHighlightsRecentlyRanked();
        $bindings['HighlightsStillUnranked'] = $this->getServiceReviewLink()->getHighlightsStillUnranked();
        $bindings['HighlightsAlreadyRanked'] = $this->getServiceReviewLink()->getHighlightsAlreadyRanked();

        $bindings['HighlightsFullList'] = $this->getServiceReviewLink()->getHighlightsFullList();

        return view('staff.reviews.highlights.show', $bindings);
    }
}
