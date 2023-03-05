<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\Game\Repository as GameRepository;

use App\Events\ReviewLinkCreated;
use App\Models\ReviewLink;

use App\Traits\StaffView;
use App\Traits\SwitchServices;

class ReviewLinkController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'required|exists:games,id',
        'site_id' => 'required|exists:review_sites,id',
        'url' => 'required',
        'rating_original' => 'required'
    ];

    private $repoReviewLink;
    private $repoReviewSite;
    private $repoGame;

    public function __construct(
        ReviewLinkRepository $repoReviewLink,
        ReviewSiteRepository $repoReviewSite,
        GameRepository $repoGame
    )
    {
        $this->repoReviewLink = $repoReviewLink;
        $this->repoReviewSite = $repoReviewSite;
        $this->repoGame = $repoGame;
    }

    public function showList()
    {
        $bindings = $this->getBindingsReviewsSubpage('Review links', "[ 3, 'desc']");

        $siteId = request()->siteId;

        $reviewSites = $this->repoReviewSite->getAll();

        if (!$siteId) {
            $bindings['ActiveSiteId'] = '';
            $tableLimit = 250;
            $reviewLinks = $this->getServiceReviewLink()->getAll($tableLimit);
            $bindings['TableLimit'] = $tableLimit;
        } else {
            $bindings['ActiveSiteId'] = $siteId;
            $reviewLinks = $this->getServiceReviewLink()->getAllBySite($siteId);
        }

        $bindings['ReviewLinks'] = $reviewLinks;
        $bindings['ReviewSites'] = $reviewSites;

        return view('staff.reviews.link.list', $bindings);
    }

    public function import()
    {
        $pageTitle = 'Import review links';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsReviewLinksSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $importData = $request->import_data;

            $reviews = explode("\n", $importData);

            if (count($reviews) == 0) {
                return redirect(route('staff.reviews.link.import'));
            }

            foreach ($reviews as $reviewRow) {

                $review = explode("\t", $reviewRow);

                $siteId = $review[0];
                $gameId = $review[1];
                $url = $review[2];
                $rating = $review[3];
                $reviewDate = $review[4];

                $reviewSite = $this->repoReviewSite->find($siteId);
                if (!$reviewSite) abort(500);
                $game = $this->repoGame->find($gameId);
                if (!$game) abort(500);

                $existingReview = $this->repoReviewLink->byGameAndSite($gameId, $siteId);
                if ($existingReview) continue;

                $ratingNormalised = $this->repoReviewLink->getNormalisedRating($rating, $reviewSite->rating_scale);

                $reviewLink = $this->repoReviewLink->create(
                    $gameId, $siteId, $url, $rating, $ratingNormalised, $reviewDate
                );

                // Update game review stats
                $reviewLinks = $this->getServiceReviewLink()->getByGame($gameId);
                $quickReviews = $this->getServiceQuickReview()->getActiveByGame($gameId);
                $this->getServiceReviewStats()->updateGameReviewStats($game, $reviewLinks, $quickReviews);

            }

            // All done; send us back
            return redirect(route('staff.reviews.link.list'));

        }

        return view('staff.reviews.link.import', $bindings);
    }

    public function add()
    {
        $bindings = $this->getBindingsReviewsLinkListSubpage('Add review link');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $this->repoReviewSite->find($siteId);
            $gameId = $request->game_id;

            $ratingNormalised = $this->getServiceReviewLink()->getNormalisedRating($ratingOriginal, $reviewSite);

            $reviewLink = $this->getServiceReviewLink()->create(
                $gameId, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date, ReviewLink::TYPE_MANUAL, $request->description
            );

            // Update game review stats
            $game = $this->getServiceGame()->find($gameId);
            $reviewLinks = $this->getServiceReviewLink()->getByGame($gameId);
            $quickReviews = $this->getServiceQuickReview()->getActiveByGame($gameId);
            $this->getServiceReviewStats()->updateGameReviewStats($game, $reviewLinks, $quickReviews);

            // Trigger event
            event(new ReviewLinkCreated($reviewLink));

            // All done; send us back
            return redirect(route('staff.reviews.link.list').'?siteId='.$siteId);

        }

        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        $bindings['ReviewSites'] = $this->repoReviewSite->getAll();

        return view('staff.reviews.link.add', $bindings);
    }

    public function edit($linkId)
    {
        $bindings = $this->getBindingsReviewsLinkListSubpage('Edit review link');

        $reviewLinkData = $this->getServiceReviewLink()->find($linkId);
        if (!$reviewLinkData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $this->repoReviewSite->find($siteId);
            $gameId = $request->game_id;

            $ratingNormalised = $this->getServiceReviewLink()->getNormalisedRating($ratingOriginal, $reviewSite);

            $this->getServiceReviewLink()->edit(
                $reviewLinkData,
                $gameId, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date, $request->description
            );

            // Update game review stats
            $game = $this->getServiceGame()->find($gameId);
            $reviewLinks = $this->getServiceReviewLink()->getByGame($gameId);
            $quickReviews = $this->getServiceQuickReview()->getActiveByGame($gameId);
            $this->getServiceReviewStats()->updateGameReviewStats($game, $reviewLinks, $quickReviews);

            // Update ranks
            //\Artisan::call('UpdateGameRanks');

            // All done; send us back
            return redirect(route('staff.reviews.link.list').'?siteId='.$siteId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['ReviewLinkData'] = $reviewLinkData;
        $bindings['LinkId'] = $linkId;

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        $bindings['ReviewSites'] = $this->repoReviewSite->getAll();

        return view('staff.reviews.link.edit', $bindings);
    }

    public function delete($linkId)
    {
        $bindings = $this->getBindingsReviewsLinkListSubpage('Delete review link');

        $reviewLink = $this->getServiceReviewLink()->find($linkId);
        if (!$reviewLink) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $gameId = $request->game_id;

            $this->getServiceReviewLink()->delete($linkId);

            $game = $this->getServiceGame()->find($reviewLink->game_id);
            if ($game) {
                // Update game review stats
                $reviewLinks = $this->getServiceReviewLink()->getByGame($gameId);
                $quickReviews = $this->getServiceQuickReview()->getActiveByGame($gameId);
                $this->getServiceReviewStats()->updateGameReviewStats($game, $reviewLinks, $quickReviews);
            }

            // Done

            return redirect(route('staff.reviews.link.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['ReviewLinkData'] = $reviewLink;
        $bindings['LinkId'] = $linkId;

        return view('staff.reviews.link.delete', $bindings);
    }

}
