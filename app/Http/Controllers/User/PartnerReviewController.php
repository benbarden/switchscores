<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class PartnerReviewController extends Controller
{
    use SwitchServices;
    use AuthUser;

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
        $userId = $this->getAuthId();
        $partnerId = $this->getValidUser($this->getServiceUser())->partner_id;

        $request = request();

        $serviceGame = $this->getServiceGame();
        $servicePartnerReview = $this->getServicePartnerReview();
        $serviceReviewLink = $this->getServiceReviewLink();

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

            $validator->after(function ($validator) use ($serviceReviewLink, $gameId, $partnerId, $itemDate) {

                // Check for an existing review
                $reviewLinkExisting = $serviceReviewLink->getByGameAndSite($gameId, $partnerId);
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

            $partnerReview = $servicePartnerReview->create(
                $userId, $partnerId, $gameId, $itemUrl, $itemDate, $itemRating
            );

            return redirect(route('user.partner-reviews.list').'?msg=success');

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Add review';
        $bindings['PageTitle'] = 'Add review';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll();

        return view('user.partner-reviews.add', $bindings);
    }

    public function showList()
    {
        $urlMsg = \Request::get('msg');

        $servicePartnerReview = $this->getServicePartnerReview();

        $userId = $this->getAuthId();
        $partnerId = $this->getValidUser($this->getServiceUser())->partner_id;

        if ($partnerId == 0) {
            abort(403);
        }

        $bindings = [];

        $bindings['TopTitle'] = 'Partner reviews';
        $bindings['PageTitle'] = 'Partner reviews';

        if ($urlMsg) {
            $bindings['MsgSuccess'] = true;
        }

        $bindings['ReviewPartnerList'] = $servicePartnerReview->getAllBySite($partnerId);

        return view('user.partner-reviews.list', $bindings);
    }
}
