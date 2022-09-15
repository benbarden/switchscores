<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\User\Repository as UserRepository;

use App\Traits\SwitchServices;

class CommunityController extends Controller
{
    use SwitchServices;

    private $repoUser;

    public function __construct(
        UserRepository $repoUser
    )
    {
        $this->repoUser = $repoUser;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['QuickReviews'] = $this->getServiceQuickReview()->getLatestActive(5);
        $bindings['HallOfFame'] = $this->repoUser->getMostPoints(5);

        // Stats
        $bindings['NewestUser'] = $this->repoUser->getNewest();
        $bindings['CollectionCount'] = $this->getServiceUserGamesCollection()->countAllCollections();


        $bindings['TopTitle'] = 'Community';
        $bindings['PageTitle'] = 'Community';

        return view('community.landing', $bindings);
    }
}
