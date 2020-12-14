<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Services\Migrations\Category as MigrationsCategory;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class IndexController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function show()
    {
        $servicePartner = $this->getServicePartner();
        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceCollection = $this->getServiceUserGamesCollection();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $siteRole = 'member'; // default

        $userId = $this->getAuthId();

        $authUser = $this->getValidUser($this->getServiceUser());

        $onPageTitle = 'Members dashboard';

        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

        $partnerId = $authUser->partner_id;

        if ($partnerId) {

            $partnerData = $servicePartner->find($partnerId);

            if ($partnerData) {

                $bindings['PartnerData'] = $partnerData;

                if ($partnerData->isReviewSite()) {

                    $siteRole = 'review-partner';

                } elseif ($partnerData->isGamesCompany()) {

                    $siteRole = 'games-company';

                    $onPageTitle = 'Games company dashboard: '.$partnerData->name;

                    // Recent games
                    $gameDevList = $serviceGameDeveloper->getGamesByDeveloper($partnerId, false, 5);
                    $gamePubList = $serviceGamePublisher->getGamesByPublisher($partnerId, false, 5);
                    $bindings['GameDevList'] = $gameDevList;
                    $bindings['GamePubList'] = $gamePubList;

                }

            }

        }

        $bindings['TopTitle'] = $onPageTitle;
        $bindings['PageTitle'] = $onPageTitle;
        $bindings['SiteRole'] = $siteRole;

        // Database help
        $serviceMigrationsCategory = new MigrationsCategory();
        $bindings['NoCategoryCount'] = $serviceMigrationsCategory->countGamesWithNoCategory();

        return view('user.index', $bindings);
    }
}
