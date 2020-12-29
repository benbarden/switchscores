<?php

namespace App\Http\Controllers\Staff\Reviews;

use App\ReviewFeedImport;
use App\Traits\StaffView;
use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class FeedImportsController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Reviews - Feed imports';
        $bindings['PageTitle'] = 'Feed imports';

        $serviceReviewFeedImport = $this->getServiceReviewFeedImport();

        $bindings['ImportList'] = $serviceReviewFeedImport->getAll();

        return view('staff.reviews.feed-imports.list', $bindings);
    }

    public function showItemList(ReviewFeedImport $feedImport)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Reviews - Feed imports - Item list';
        $bindings['PageTitle'] = 'Feed imports: Item list';

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();

        $bindings['FeedImport'] = $feedImport;
        $bindings['ItemList'] = $serviceReviewFeedItem->getByImportId($feedImport->id);

        return view('staff.reviews.feed-imports.import-items', $bindings);
    }
}
