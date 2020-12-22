<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\MemberView;

class QuickReviewController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use MemberView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'game_id' => 'required|exists:games,id',
        'review_score' => 'required|numeric|between:0,10',
        'review_body' => 'max:800'
    ];

    public function add()
    {
        $onPageTitle = 'Add quick review';

        $bindings = $this->getBindingsQuickReviewsSubpage($onPageTitle);

        $userId = $this->getAuthId();

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

        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll();

        $scoreList = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $bindings['ScoreList'] = $scoreList;

        $urlGameId = $request->gameId;
        if ($urlGameId) {
            $bindings['UrlGameId'] = $urlGameId;
        }

        return view('user.quick-reviews.add', $bindings);
    }

    public function showList()
    {
        $onPageTitle = 'Quick reviews';

        $bindings = $this->getBindingsDashboardGenericSubpage($onPageTitle);

        $urlMsg = \Request::get('msg');

        $userId = $this->getAuthId();

        if ($urlMsg) {
            $bindings['MsgSuccess'] = true;
        }

        $bindings['ReviewList'] = $this->getServiceQuickReview()->getAllByUser($userId);

        return view('user.quick-reviews.list', $bindings);
    }
}
