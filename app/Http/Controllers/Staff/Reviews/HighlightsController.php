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

        $bindings = [];

        $bindings['HighlightsList'] = $this->getServiceReviewLink()->getLatestReviewsForHighlights(7);

        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.reviews.highlights.show', $bindings);
    }
}
