<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Models\User;
use App\Services\UserService;

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
        $repoReviewSite = new ReviewSiteRepository();

        $authUser = $this->getValidUser($this->getServiceUser());
        $partnerId = $authUser->partner_id;
        $reviewSite = $repoReviewSite->find($partnerId);

        if (!$reviewSite) {
            return null;
        } else {
            return $partnerId;
        }
    }
}