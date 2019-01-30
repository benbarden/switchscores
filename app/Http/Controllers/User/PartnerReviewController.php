<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;
use Auth;

class PartnerReviewController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'required|exists:games,id',
        'item_url' => 'required|max:200',
        'item_date' => 'required',
        'item_rating' => 'required|numeric|between:0,10',
    ];

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userId = Auth::id();
        $regionCode = Auth::user()->region;
        $userSiteId = Auth::user()->site_id;

        $request = request();

        $gameService = $serviceContainer->getGameService();
        $partnerReviewService = $serviceContainer->getPartnerReviewService();
        $reviewLinkService = $serviceContainer->getReviewLinkService();

        if ($request->isMethod('post')) {

            $gameId = $request->game_id;
            $itemUrl = $request->item_url;
            $itemDate = $request->item_date;
            $itemRating = $request->item_rating;

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('user.partner-reviews.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $validator->after(function ($validator) use ($reviewLinkService, $gameId, $userSiteId, $itemDate) {

                // Check for an existing review
                $reviewLinkExisting = $reviewLinkService->getByGameAndSite($gameId, $userSiteId);
                if ($reviewLinkExisting) {
                    $validator->errors()->add('game_id', 'We already have a review from your site for this game. Please try a different game.');
                }

                // Block zero dates
                if ($itemDate == '0000-00-00') {
                    $validator->errors()->add('item_date', 'Please enter a valid date.');
                }

            });

            if ($validator->fails()) {
                return redirect(route('user.partner-reviews.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // OK to proceed

            $partnerReview = $partnerReviewService->create(
                $userId, $userSiteId, $gameId, $itemUrl, $itemDate, $itemRating
            );

            return redirect(route('user.partner-reviews.list').'?msg=success');

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Add review';
        $bindings['PageTitle'] = 'Add review';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $gameService->getAll($regionCode);

        return view('user.partner-reviews.add', $bindings);
    }

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $urlMsg = \Request::get('msg');

        $partnerReviewService = $serviceContainer->getPartnerReviewService();

        $userId = Auth::id();
        $userRegion = Auth::user()->region;
        $userSiteId = Auth::user()->site_id;

        if ($userSiteId == 0) {
            abort(403);
        }

        $bindings = [];

        $bindings['TopTitle'] = 'Partner reviews';
        $bindings['PageTitle'] = 'Partner reviews';

        $bindings['UserRegion'] = $userRegion;

        if ($urlMsg) {
            $bindings['MsgSuccess'] = true;
        }

        $bindings['ReviewPartnerList'] = $partnerReviewService->getAllBySite($userSiteId);

        return view('user.partner-reviews.list', $bindings);
    }
}
