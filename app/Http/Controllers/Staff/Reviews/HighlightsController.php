<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class HighlightsController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Review highlights';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['HighlightsRecentlyRanked'] = $this->getServiceReviewLink()->getHighlightsRecentlyRanked();
        $bindings['HighlightsStillUnranked'] = $this->getServiceReviewLink()->getHighlightsStillUnranked();
        $bindings['HighlightsAlreadyRanked'] = $this->getServiceReviewLink()->getHighlightsAlreadyRanked();

        $bindings['HighlightsFullList'] = $this->getServiceReviewLink()->getHighlightsFullList();

        return view('staff.reviews.highlights.show', $bindings);
    }
}
