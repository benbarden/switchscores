<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\Game\Repository as GameRepository;

use App\Traits\SwitchServices;

class QuickReviewController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'review_score' => 'required|numeric|between:0,10',
        'review_body' => 'required|max:800'
    ];

    public function __construct(
        private GameRepository $repoGame
    ){

    }

    public function add($gameId)
    {
        $pageTitle = 'Add quick review';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->quickReviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        // Don't allow duplicate reviews
        $reviewedGameIdList = $this->getServiceQuickReview()->getAllByUserGameIdList($userId);
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
        $pageTitle = 'Quick reviews';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $urlMsg = \Request::get('msg');

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        if ($urlMsg) {
            $bindings['MsgSuccess'] = true;
        }

        $bindings['ReviewList'] = $this->getServiceQuickReview()->getAllByUser($userId);

        return view('user.quick-reviews.list', $bindings);
    }
}
