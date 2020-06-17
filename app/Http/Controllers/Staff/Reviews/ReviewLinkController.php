<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\ReviewLink;

use App\Events\ReviewLinkCreated;

use App\Traits\SwitchServices;

class ReviewLinkController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'required|exists:games,id',
        'site_id' => 'required|exists:partners,id',
        'url' => 'required',
        'rating_original' => 'required'
    ];

    public function showList()
    {
        $serviceReviewLink = $this->getServiceReviewLink();
        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $siteId = request()->siteId;

        $reviewSites = $servicePartner->getAllReviewSites();

        $bindings['TopTitle'] = 'Staff - Review links';
        $bindings['PageTitle'] = 'Review links';

        $jsInitialSort = "[ 3, 'desc']";

        if (!$siteId) {
            $bindings['ActiveSiteId'] = '';
            $tableLimit = 250;
            $reviewLinks = $serviceReviewLink->getAll($tableLimit);
            $bindings['TableLimit'] = $tableLimit;
        } else {
            $bindings['ActiveSiteId'] = $siteId;
            $reviewLinks = $serviceReviewLink->getAllBySite($siteId);
        }

        $bindings['ReviewLinks'] = $reviewLinks;
        $bindings['ReviewSites'] = $reviewSites;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('staff.reviews.link.list', $bindings);
    }

    public function add()
    {
        $request = request();

        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceGame = $this->getServiceGame();
        $serviceReviewStats = $this->getServiceReviewStats();
        $servicePartner = $this->getServicePartner();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $servicePartner->find($siteId);

            $ratingNormalised = $serviceReviewLink->getNormalisedRating($ratingOriginal, $reviewSite);

            $reviewLink = $serviceReviewLink->create(
                $request->game_id, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date, ReviewLink::TYPE_MANUAL
            );

            // Update game review stats
            $game = $serviceGame->find($request->game_id);
            $serviceReviewStats->updateGameReviewStats($game);

            // Trigger event
            event(new ReviewLinkCreated($reviewLink));

            // All done; send us back
            return redirect(route('staff.reviews.link.list').'?siteId='.$siteId);

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Review links - Add link';
        $bindings['PageTitle'] = 'Add review link';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll();

        $bindings['ReviewSites'] = $servicePartner->getAllReviewSites();

        return view('staff.reviews.link.add', $bindings);
    }

    public function edit($linkId)
    {
        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceGame = $this->getServiceGame();
        $serviceReviewStats = $this->getServiceReviewStats();
        $servicePartner = $this->getServicePartner();

        $reviewLinkData = $serviceReviewLink->find($linkId);
        if (!$reviewLinkData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $servicePartner->find($siteId);

            $ratingNormalised = $serviceReviewLink->getNormalisedRating($ratingOriginal, $reviewSite);

            $serviceReviewLink->edit(
                $reviewLinkData,
                $request->game_id, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date
            );

            // Update game review stats
            $game = $serviceGame->find($request->game_id);
            $serviceReviewStats->updateGameReviewStats($game);

            // Update ranks
            //\Artisan::call('UpdateGameRanks');

            // All done; send us back
            return redirect(route('staff.reviews.link.list').'?siteId='.$siteId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Staff - Review links - Edit link';
        $bindings['PageTitle'] = 'Edit review link';
        $bindings['ReviewLinkData'] = $reviewLinkData;
        $bindings['LinkId'] = $linkId;

        $bindings['GamesList'] = $serviceGame->getAll();

        $bindings['ReviewSites'] = $servicePartner->getAllReviewSites();

        return view('staff.reviews.link.edit', $bindings);
    }

    public function delete($linkId)
    {
        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceGame = $this->getServiceGame();

        $reviewLink = $serviceReviewLink->find($linkId);
        if (!$reviewLink) abort(404);

        $bindings = [];

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceReviewLink->delete($linkId);

            $game = $serviceGame->find($reviewLink->game_id);
            if ($game) {
                // Update game review stats
                $this->getServiceReviewStats()->updateGameReviewStats($game);
            }

            // Done

            return redirect(route('staff.reviews.link.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Staff - Review links - Delete link';
        $bindings['PageTitle'] = 'Delete link';
        $bindings['ReviewLinkData'] = $reviewLink;
        $bindings['LinkId'] = $linkId;

        return view('staff.reviews.link.delete', $bindings);
    }

}
