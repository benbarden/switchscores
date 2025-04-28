<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;
use App\Domain\QuickReview\Repository as QuickReviewRepository;

use App\Events\ReviewLinkCreated;
use App\Models\ReviewLink;

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
        'site_id' => 'required|exists:review_sites,id',
        'url' => 'required',
        'rating_original' => 'required'
    ];

    public function __construct(
        private ReviewLinkRepository $repoReviewLink,
        private ReviewSiteRepository $repoReviewSite,
        private GameRepository $repoGame,
        private ReviewLinkStats $reviewLinkStats,
        private QuickReviewRepository $repoQuickReview
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'Review links';
        $tableSort = "[ 3, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $siteId = request()->siteId;

        $reviewSites = $this->repoReviewSite->getAll();

        if (!$siteId) {
            $bindings['ActiveSiteId'] = '';
            $tableLimit = 250;
            $reviewLinks = $this->repoReviewLink->recentlyAdded($tableLimit);
            $bindings['TableLimit'] = $tableLimit;
        } else {
            $bindings['ActiveSiteId'] = $siteId;
            $reviewLinks = $this->repoReviewLink->bySite($siteId);
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
                $quickReviews = $this->repoQuickReview->byGameActive($gameId);
                $this->getServiceReviewStats()->updateGameReviewStats($game, $reviewLinks, $quickReviews);

            }

            // All done; send us back
            return redirect(route('staff.reviews.link.list'));

        }

        return view('staff.reviews.link.import', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add review link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsReviewLinksSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $siteId = $request->site_id;
            $ratingOriginal = $request->rating_original;

            $reviewSite = $this->repoReviewSite->find($siteId);
            $gameId = $request->game_id;

            $ratingNormalised = $this->getServiceReviewLink()->getNormalisedRating($ratingOriginal, $reviewSite);

            $reviewLink = $this->repoReviewLink->create(
                $gameId, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date, ReviewLink::TYPE_MANUAL, $request->description
            );

            // Update game review stats
            $game = $this->repoGame->find($gameId);
            $this->reviewLinkStats->updateStats($game);

            // Trigger event
            event(new ReviewLinkCreated($reviewLink));

            // All done; send us back
            return redirect(route('staff.reviews.link.list').'?siteId='.$siteId);

        }

        $bindings['FormMode'] = 'add';

        $bindings['ReviewSites'] = $this->repoReviewSite->getAll();

        return view('staff.reviews.link.add', $bindings);
    }

    public function edit($linkId)
    {
        $pageTitle = 'Edit review link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsReviewLinksSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $reviewLinkData = $this->repoReviewLink->find($linkId);
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

            $this->repoReviewLink->edit(
                $reviewLinkData,
                $gameId, $siteId, $request->url, $ratingOriginal, $ratingNormalised,
                $request->review_date, $request->description
            );

            // Update game review stats
            $game = $this->repoGame->find($gameId);
            $this->reviewLinkStats->updateStats($game);

            // Update ranks
            //\Artisan::call('UpdateGameRanks');

            // All done; send us back
            return redirect(route('staff.reviews.link.list').'?siteId='.$siteId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['ReviewLinkData'] = $reviewLinkData;
        $bindings['LinkId'] = $linkId;

        $bindings['ReviewSites'] = $this->repoReviewSite->getAll();

        return view('staff.reviews.link.edit', $bindings);
    }

    public function delete($linkId)
    {
        $pageTitle = 'Delete review link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsReviewLinksSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $reviewLink = $this->repoReviewLink->find($linkId);
        if (!$reviewLink) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $gameId = $request->game_id;

            $this->repoReviewLink->delete($linkId);

            $game = $this->repoGame->find($reviewLink->game_id);
            if ($game) {
                // Update game review stats
                $this->reviewLinkStats->updateStats($game);
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
