<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewDraft\Builder as ReviewDraftBuilder;
use App\Domain\ReviewDraft\Director as ReviewDraftDirector;
use App\Domain\GameLists\Repository as GameListsRepository;

use App\Traits\SwitchServices;

class ReviewDraftsController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        //'site_id' => 'required',
    ];

    public function __construct(
        private ReviewDraftRepository $repoReviewDraft,
        private GameListsRepository $repoGameLists
    )
    {
    }

    public function showPending()
    {
        $pageTitle = 'Review drafts';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['jsInitialSort'] = '[0, "asc"]';

        $bindings['ReviewDraftItems'] = $this->repoReviewDraft->getUnprocessed();

        return view('staff.reviews.review-drafts.list', $bindings);
    }

    public function byProcessStatus($status)
    {
        $pageTitle = 'Review drafts';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['ReviewDraftItems'] = $this->repoReviewDraft->getByProcessStatus($status);

        $bindings['HideFilters'] = 'Y';

        return view('staff.reviews.review-drafts.list', $bindings);
    }

    public function edit($itemId)
    {
        $pageTitle = 'Edit review draft';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsReviewDraftsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $reviewDraft = $this->repoReviewDraft->find($itemId);
        if (!$reviewDraft) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->reviewDraftBuilder = new ReviewDraftBuilder();
            $this->reviewDraftBuilder->setReviewDraft($reviewDraft);
            $this->reviewDraftDirector = new ReviewDraftDirector($this->reviewDraftBuilder);

            $reviewDraftData = [
                'game_id' => $request->game_id,
                'item_rating' => $request->item_rating,
                'process_status' => $request->process_status,
            ];

            $this->reviewDraftDirector->buildExisting($reviewDraft, $reviewDraftData);
            $this->reviewDraftDirector->save();

            // All done; send us back
            return redirect(route('staff.reviews.review-drafts.showPending'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['ReviewDraftData'] = $reviewDraft;
        $bindings['ItemId'] = $itemId;

        //$bindings['GamesList'] = $this->repoGameLists->getAll();

        $bindings['ProcessStatusSuccess'] = $this->repoReviewDraft->getProcessOptionsSuccess();
        $bindings['ProcessStatusFailure'] = $this->repoReviewDraft->getProcessOptionsFailure();

        return view('staff.reviews.review-drafts.edit', $bindings);
    }
}
