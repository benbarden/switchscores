<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\ReviewFeedImport;

class FeedImportsController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsReviewsSubpage('Feed imports');

        $bindings['ImportList'] = $this->getServiceReviewFeedImport()->getAll();

        return view('staff.reviews.feed-imports.list', $bindings);
    }

    public function showItemList(ReviewFeedImport $feedImport)
    {
        $bindings = $this->getBindingsReviewsFeedImportsSubpage('Feed imports - Item list');

        $bindings['FeedImport'] = $feedImport;
        $bindings['ItemList'] = $this->getServiceReviewFeedItem()->getByImportId($feedImport->id);

        return view('staff.reviews.feed-imports.import-items', $bindings);
    }
}
