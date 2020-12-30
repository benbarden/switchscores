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
    private $validationRulesFindGame = [
        'search_keywords' => 'required|min:3',
    ];

    /**
     * @var array
     */
    private $validationRules = [
        'review_score' => 'required|numeric|between:0,10',
        'review_body' => 'required|max:800'
    ];

    public function findGame()
    {
        $bindings = $this->getBindingsQuickReviewsSubpage('Add quick review: Find game');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesFindGame);

            $keywords = request()->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->getServiceGame()->searchByTitle($keywords);
            }

        }

        $bindings['ReviewedGameIdList'] = $this->getServiceQuickReview()->getAllByUserGameIdList($this->getAuthId());

        return view('user.quick-reviews.game-search', $bindings);
    }

    public function add($gameId)
    {
        $bindings = $this->getBindingsQuickReviewsSubpage('Add quick review');

        $userId = $this->getAuthId();

        $gameData = $this->getServiceGame()->find($gameId);
        if (!$gameData) abort(404);

        // Don't allow duplicate reviews
        $reviewedGameIdList = $this->getServiceQuickReview()->getAllByUserGameIdList($this->getAuthId());
        if ($reviewedGameIdList->contains($gameId)) {
            return redirect(route('user.quick-reviews.list'));
        }

        $request = request();

        $reviewBody = $request->review_body;
        $reviewBody = strip_tags($reviewBody);
        $reviewBody = nl2br($reviewBody);

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $quickReview = $this->getServiceQuickReview()->create(
                $userId, $gameId, $request->review_score, $reviewBody
            );

            return redirect(route('user.quick-reviews.list').'?msg=success');

        }

        $bindings['FormMode'] = 'add';
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;

        return view('user.quick-reviews.add', $bindings);
    }

    public function showList()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Quick reviews');

        $urlMsg = \Request::get('msg');

        $userId = $this->getAuthId();

        if ($urlMsg) {
            $bindings['MsgSuccess'] = true;
        }

        $bindings['ReviewList'] = $this->getServiceQuickReview()->getAllByUser($userId);

        return view('user.quick-reviews.list', $bindings);
    }
}
