<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;
use Auth;

class ReviewUserController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'required|exists:games,id',
        'quick_rating' => 'required',
        'review_score' => 'required|numeric|between:0,10',
        'review_body' => 'max:500'
    ];

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userId = Auth::id();
        $regionCode = Auth::user()->region;

        $request = request();

        $gameService = $serviceContainer->getGameService();
        $reviewUserService = $serviceContainer->getReviewUserService();
        $reviewQuickRatingService = $serviceContainer->getReviewQuickRatingService();

        $reviewBody = $request->review_body;
        $reviewBody = strip_tags($reviewBody);
        $reviewBody = nl2br($reviewBody);

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $reviewUser = $reviewUserService->create(
                $userId, $request->game_id, $request->quick_rating,
                $request->review_score, $reviewBody
            );

            return redirect(route('user.reviews.list').'?msg=success');

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Add review';
        $bindings['PageTitle'] = 'Add review';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $gameService->getAll($regionCode);

        $bindings['QuickRatingList'] = $reviewQuickRatingService->getAll();

        $urlGameId = $request->gameId;
        if ($urlGameId) {
            $bindings['UrlGameId'] = $urlGameId;
        }

        return view('user.reviews.add', $bindings);
    }

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $urlMsg = \Request::get('msg');

        $reviewUserService = $serviceContainer->getReviewUserService();

        $userId = Auth::id();

        $bindings = [];

        $bindings['TopTitle'] = 'Quick user reviews';
        $bindings['PageTitle'] = 'Quick user reviews';

        $bindings['UserRegion'] = Auth::user()->region;

        if ($urlMsg) {
            $bindings['MsgSuccess'] = true;
        }

        $bindings['ReviewUserList'] = $reviewUserService->getAllByUser($userId);

        return view('user.reviews.list', $bindings);
    }
}
