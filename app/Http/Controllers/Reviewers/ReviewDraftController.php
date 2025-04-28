<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Models\ReviewDraft;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewDraft\Builder as ReviewDraftBuilder;
use App\Domain\ReviewDraft\Director as ReviewDraftDirector;
use App\Domain\Game\Repository as GameRepository;

class ReviewDraftController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRulesFindGame = [
        'search_keywords' => 'required|min:3',
    ];

    /**
     * @var array
     */
    private $validationRulesFeedItem = [
    ];

    public function __construct(
        private ReviewDraftRepository $repoReviewDraft,
        private ReviewSiteRepository $repoReviewSite,
        private ReviewLinkRepository $repoReviewLink,
        private GameRepository $repoGame
    )
    {
    }

    public function findGame()
    {
        $pageTitle = 'Add manual review: Find game';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();
        $siteId = $currentUser->partner_id;

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesFindGame);

            $keywords = request()->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->repoGame->searchByTitle($keywords);
            }

        }

        $bindings['jsInitialSort'] = "[0, 'desc']";

        $bindings['ReviewLinkIdList'] = $this->repoReviewLink->bySiteGameIdList($siteId);

        return view('reviewers.reviews.review-draft.game-search', $bindings);
    }

    public function validateForm(&$validator, $partnerData, $request, $partnerUrl, $isYoutubeChannel, $reviewDraft = null)
    {
        $validator->after(function ($validator) use ($partnerData, $request, $partnerUrl, $isYoutubeChannel, $reviewDraft) {

            if (!$request->item_url) {
                $validator->errors()->add('title', 'Please enter a URL.');
            }
            if (!$request->item_date) {
                $validator->errors()->add('title', 'Please enter a date.');
            }
            if (!$request->item_rating) {
                $validator->errors()->add('title', 'Please enter a rating.');
            }

            $feedItemUrl = $request->item_url;

            // Check URL hasn't already been submitted
            if ($reviewDraft) {
                $existingReviewDraft = $this->repoReviewDraft->getByItemUrl($feedItemUrl, $reviewDraft->id);
            } else {
                $existingReviewDraft = $this->repoReviewDraft->getByItemUrl($feedItemUrl);
            }
            if ($existingReviewDraft) {
                $validator->errors()->add('title', 'The URL you\'ve entered matches an existing review draft. Please try another.');
            }
            $existingReviewLink = $this->repoReviewLink->byUrl($feedItemUrl);
            if ($existingReviewLink) {
                $validator->errors()->add('title', 'The URL you\'ve entered matches an existing review link. Please try another.');
            }

            // Check URL starts with the partner domain name
            if ($feedItemUrl) {

                if ($feedItemUrl == $partnerUrl) {
                    $validator->errors()->add('title', 'The URL needs to include a full link to the review, not just your homepage link.');
                }

                // Handle Youtube channels differently
                if ($isYoutubeChannel) {
                    $youtubeMatchLinkFull = 'https://www.youtube.com/watch?v=';
                    $youtubeMatchLinkShort = 'https://youtu.be/';
                    if (substr($feedItemUrl, 0, strlen($youtubeMatchLinkFull)) == $youtubeMatchLinkFull) {
                        // OK
                    } elseif (substr($feedItemUrl, 0, strlen($youtubeMatchLinkShort)) == $youtubeMatchLinkShort) {
                        // OK
                    } else {
                        $validator->errors()->add('title', 'The URL you\'ve entered doesn\'t appear to be a YouTube link. Please try another.');
                    }
                } elseif (substr($feedItemUrl, 0, strlen($partnerUrl)) != $partnerUrl) {
                    $validator->errors()->add('title', 'The URL you\'ve entered doesn\'t appear to be from your site. Please try another.');
                }

            }

            // Check rating doesn't exceed the scale
            if ($request->item_rating > $partnerData->rating_scale) {
                $errorMsg = sprintf('You\'ve entered a rating of %s, but your site ranks games out of %s.', $request->item_rating, $partnerData->rating_scale);
                $validator->errors()->add('title', $errorMsg);
            }

        });

    }

    public function add($gameId)
    {
        $pageTitle = 'Add manual review';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;
        $siteId = $currentUser->partner_id;

        if (!$siteId) abort(403);

        $partnerData = $this->repoReviewSite->find($siteId);
        $partnerUrl = $partnerData->website_url;

        $isYoutubeChannel = $partnerData->isYoutubeChannel();

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(400);

        $reviewLinkIdList = $this->repoReviewLink->bySiteGameIdList($siteId);
        /* @var $reviewLinkIdList \Illuminate\Support\Collection */
        if ($reviewLinkIdList->contains($gameId)) {
            //abort(500);
            return redirect(route('reviewers.index'));
        }

        $request = request();

        if ($request->isMethod('post')) {

            // Run initial validation rules
            $validator = Validator::make($request->all(), $this->validationRulesFeedItem);

            // Custom rules
            $this->validateForm($validator, $partnerData, $request, $partnerUrl, $isYoutubeChannel);

            if ($validator->fails()) {
                return redirect(route('reviewers.review-draft.add', ['gameId' => $gameId]))
                    ->withErrors($validator)
                    ->withInput();
            }

            // OK to proceed
            $params = [
                'user_id' => $userId,
                'site_id' => $siteId,
                'game_id' => $gameId,
                'item_title' => 'Review of '.$gameData->title,
                'item_url' => $request->item_url,
                'item_date' => $request->item_date,
                'item_rating' => $request->item_rating,
            ];
            $reviewDraftBuilder = new ReviewDraftBuilder();
            $reviewDraftDirector = new ReviewDraftDirector($reviewDraftBuilder);

            $reviewDraftDirector->buildNewFeed($params);
            $reviewDraftDirector->save();

            return redirect(route('reviewers.index'));
        }

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['ReviewSite'] = $partnerData;

        $bindings['IsYoutubeChannel'] = $isYoutubeChannel;

        return view('reviewers.reviews.review-draft.add', $bindings);
    }

    public function edit(ReviewDraft $reviewDraft)
    {
        $pageTitle = 'Edit manual review';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();
        $siteId = $currentUser->partner_id;

        $partnerData = $this->repoReviewSite->find($siteId);
        $partnerUrl = $partnerData->website_url;

        $isYoutubeChannel = $partnerData->isYoutubeChannel();

        $gameId = $reviewDraft->game_id;
        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) {
            return redirect(route('reviewers.index'));
        }

        $request = request();

        if ($request->isMethod('post')) {

            // Run initial validation rules
            $validator = Validator::make($request->all(), $this->validationRulesFeedItem);

            // Custom rules
            $this->validateForm($validator, $partnerData, $request, $partnerUrl, $isYoutubeChannel, $reviewDraft);

            if ($validator->fails()) {
                return redirect(route('reviewers.review-draft.edit', ['reviewDraft' => $reviewDraft]))
                    ->withErrors($validator)
                    ->withInput();
            }

            // OK to proceed
            $params = [
                'item_url' => $request->item_url,
                'item_date' => $request->item_date,
                'item_rating' => $request->item_rating,
            ];
            $reviewDraftBuilder = new ReviewDraftBuilder();
            $reviewDraftDirector = new ReviewDraftDirector($reviewDraftBuilder);

            $reviewDraftDirector->buildExisting($reviewDraft, $params);
            $reviewDraftDirector->save();

            return redirect(route('reviewers.index'));
        }

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['ReviewSite'] = $partnerData;
        $bindings['ReviewDraft'] = $reviewDraft;
        $bindings['FormMode'] = 'edit';

        $bindings['IsYoutubeChannel'] = $isYoutubeChannel;

        return view('reviewers.reviews.review-draft.edit', $bindings);
    }
}
