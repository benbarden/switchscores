<?php

namespace App\Http\Controllers\Staff\Reviews;

use App\Models\ReviewFeedImport;
use App\Traits\StaffView;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

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

        $importId = $feedImport->id;

        $bindings['FeedImport'] = $feedImport;

        if ($feedImport->isTest()) {
            $itemList = $this->getServiceReviewFeedItemTest()->getByImportId($importId);
        } else {
            $itemList = $this->getServiceReviewFeedItem()->getByImportId($importId);
        }

        $bindings['ItemList'] = $itemList;

        return view('staff.reviews.feed-imports.import-items', $bindings);
    }
}
