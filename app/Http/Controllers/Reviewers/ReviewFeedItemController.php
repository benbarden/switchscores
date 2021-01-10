<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class ReviewFeedItemController extends Controller
{
    use SwitchServices;
    use AuthUser;
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

    public function findGame()
    {
        $siteId = $this->getCurrentUserReviewSiteId();
        if (!$siteId) abort(403);

        $bindings = [];

        $pageTitle = 'Add manual feed item: Find game';

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesFindGame);

            $keywords = request()->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->getServiceGame()->searchByTitle($keywords);
            }

        }

        $bindings['TopTitle'] = $pageTitle.' - Reviewers';
        $bindings['PageTitle'] = $pageTitle;

        $bindings['jsInitialSort'] = "[0, 'desc']";

        $bindings['ReviewLinkIdList'] = $this->getServiceReviewLink()->getGameIdsReviewedBySite($siteId);

        return view('reviewers.reviews.feed-item.game-search', $bindings);
    }

    public function add($gameId)
    {
        $siteId = $this->getCurrentUserReviewSiteId();
        if (!$siteId) abort(403);

        $partnerData = $this->getServicePartner()->find($siteId);
        $partnerUrl = $partnerData->website_url;

        $gameData = $this->getServiceGame()->find($gameId);
        if (!$gameData) abort(400);

        $reviewLinkIdList = $this->getServiceReviewLink()->getGameIdsReviewedBySite($siteId);
        /* @var $reviewLinkIdList \Illuminate\Support\Collection */
        if ($reviewLinkIdList->contains($gameId)) {
            abort(500);
            return redirect(route('reviewers.index'));
        }

        $youtubeBaseLink = 'https://youtube.com/';
        if (substr($partnerUrl, 0, strlen('https://youtube.com/')) == $youtubeBaseLink) {
            $isYoutubeChannel = true;
        } else {
            $isYoutubeChannel = false;
        }

        $bindings = [];
        $request = request();

        if ($request->isMethod('post')) {

            // Run initial validation rules
            $validator = Validator::make($request->all(), $this->validationRulesFeedItem);
            if ($validator->fails()) {
                return redirect(route('reviewers.review-feed-item.add', ['gameId' => $gameId]))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Custom rules
            $validator->after(function ($validator) use ($partnerData, $request, $partnerUrl, $isYoutubeChannel) {

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
                $existingFeedItem = $this->getServiceReviewFeedItem()->getByItemUrl($feedItemUrl);
                if ($existingFeedItem) {
                    $validator->errors()->add('title', 'The URL you\'ve entered matches an existing feed item. Please try another.');
                }
                $existingReviewLink = $this->getServiceReviewLink()->getByUrl($feedItemUrl);
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

            if ($validator->fails()) {
                return redirect(route('reviewers.review-feed-item.add', ['gameId' => $gameId]))
                    ->withErrors($validator)
                    ->withInput();
            }

            // OK to proceed
            $itemUrl = $request->item_url;
            $itemDate = $request->item_date;
            $itemRating = $request->item_rating;
            $itemTitle = 'Review of '.$gameData->title;

            $this->getServiceReviewFeedItem()->add(
                $siteId, $gameId, $itemUrl, $itemTitle, $itemDate, $itemRating
            );

            return redirect(route('reviewers.index'));
        }

        $bindings['TopTitle'] = 'Add manual feed item';
        $bindings['PageTitle'] = 'Add manual feed item';

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['ReviewSite'] = $partnerData;

        $bindings['IsYoutubeChannel'] = $isYoutubeChannel;

        return view('reviewers.reviews.feed-item.add', $bindings);
    }

    public function edit($itemId)
    {
    }
}
