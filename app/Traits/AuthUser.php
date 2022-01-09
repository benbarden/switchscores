<?php

namespace App\Traits;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

trait AuthUser
{
    public function getAuthId()
    {
        return Auth::id();
    }

    /**
     * @param UserService $serviceUser
     * @return \App\Models\User
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