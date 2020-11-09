<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

use App\Services\UserService;
use App\User;

trait AuthUser
{
    public function getAuthId()
    {
        return Auth::id();
    }

    /**
     * @param UserService $serviceUser
     * @return User
     */
    public function getValidUser(UserService $serviceUser)
    {
        $userId = Auth::id();

        return $serviceUser->find($userId);
    }

    public function getCurrentUserReviewSiteId()
    {
        $authUser = $this->getValidUser($this->getServiceUser());
        $partnerId = $authUser->partner_id;
        $partnerData = $this->getServicePartner()->find($partnerId);

        if (!$partnerData) {
            return null;
        } elseif (!$partnerData->isReviewSite()) {
            return null;
        } else {
            return $partnerId;
        }
    }
}