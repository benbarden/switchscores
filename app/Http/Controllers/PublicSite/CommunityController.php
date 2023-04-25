<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\User\Repository as UserRepository;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

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

        return view('public.community.landing', $bindings);
    }
}
