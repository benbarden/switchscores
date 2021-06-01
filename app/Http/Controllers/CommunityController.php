<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class CommunityController extends Controller
{
    use SwitchServices;

    public function __construct(
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['QuickReviews'] = $this->getServiceQuickReview()->getLatestActive(5);
        $bindings['HallOfFame'] = $this->getServiceUser()->getMostPoints(5);

        // Stats
        $bindings['NewestUser'] = $this->getServiceUser()->getNewest();
        $bindings['CollectionCount'] = $this->getServiceUserGamesCollection()->countAllCollections();


        $bindings['TopTitle'] = 'Community';
        $bindings['PageTitle'] = 'Community';

        return view('community.landing', $bindings);
    }
}
