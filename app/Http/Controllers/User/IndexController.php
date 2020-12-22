<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Services\Migrations\Category as MigrationsCategory;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\MemberView;

class IndexController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use MemberView;

    public function show()
    {
        $onPageTitle = 'Members dashboard';

        $bindings = $this->getBindingsDashboardGenericSubpage($onPageTitle);

        $siteRole = 'member'; // default

        $userId = $this->getAuthId();
        $authUser = $this->getValidUser($this->getServiceUser());
        $partnerId = $authUser->partner_id;

        if ($partnerId) {

            $partnerData = $this->getServicePartner()->find($partnerId);

            if ($partnerData) {

                $bindings['PartnerData'] = $partnerData;

                if ($partnerData->isReviewSite()) {

                    $siteRole = 'review-partner';

                } elseif ($partnerData->isGamesCompany()) {

                    $siteRole = 'games-company';

                    $onPageTitle = 'Games company dashboard: '.$partnerData->name;

                    // Recent games
                    $gameDevList = $this->getServiceGameDeveloper()->getGamesByDeveloper($partnerId, false, 5);
                    $gamePubList = $this->getServiceGamePublisher()->getGamesByPublisher($partnerId, false, 5);
                    $bindings['GameDevList'] = $gameDevList;
                    $bindings['GamePubList'] = $gamePubList;

                }

            }

        }

        $bindings['SiteRole'] = $siteRole;
        $bindings['UserData'] = $authUser;
        $bindings['TotalGames'] = $this->getServiceUserGamesCollection()->getUserTotalGames($userId);
        $bindings['TotalHours'] = $this->getServiceUserGamesCollection()->getUserTotalHours($userId);

        // Database help
        $serviceMigrationsCategory = new MigrationsCategory();
        $bindings['NoCategoryCount'] = $serviceMigrationsCategory->countGamesWithNoCategory();

        return view('user.index', $bindings);
    }
}
