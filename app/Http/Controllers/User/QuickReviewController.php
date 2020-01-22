<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;
use Auth;

use App\Traits\WosServices;
use App\Traits\SiteRequestData;

class QuickReviewController extends Controller
{
    use WosServices;
    use SiteRequestData;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'required|exists:games,id',
        'review_score' => 'required|numeric|between:0,10',
        'review_body' => 'max:500'
    ];

    public function add()
    {
        $userId = Auth::id();
        $regionCode = Auth::user()->region;

        $request = request();

        $serviceGame = $this->getServiceGame();
        $serviceQuickReview = $this->getServiceQuickReview();

        $reviewBody = $request->review_body;
        $reviewBody = strip_tags($reviewBody);
        $reviewBody = nl2br($reviewBody);

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $quickReview = $serviceQuickReview->create(
                $userId, $request->game_id, $request->review_score, $reviewBody
            );

            return redirect(route('user.quick-reviews.list').'?msg=success');

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Add quick review';
        $bindings['PageTitle'] = 'Add quick review';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll($regionCode);

        $urlGameId = $request->gameId;
        if ($urlGameId) {
            $bindings['UrlGameId'] = $urlGameId;
        }

        return view('user.quick-reviews.add', $bindings);
    }

    public function showList()
    {
        $urlMsg = \Request::get('msg');

        $serviceQuickReview = $this->getServiceQuickReview();

        $userId = Auth::id();

        $bindings = [];

        $bindings['TopTitle'] = 'Quick reviews';
        $bindings['PageTitle'] = 'Quick reviews';

        $bindings['UserRegion'] = Auth::user()->region;

        if ($urlMsg) {
            $bindings['MsgSuccess'] = true;
        }

        $bindings['ReviewList'] = $serviceQuickReview->getAllByUser($userId);

        return view('user.quick-reviews.list', $bindings);
    }
}
