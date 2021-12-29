<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewDraft\Builder as ReviewDraftBuilder;
use App\Domain\ReviewDraft\Director as ReviewDraftDirector;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ReviewDraftsController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        //'site_id' => 'required',
    ];

    protected $viewBreadcrumbs;
    protected $repoReviewDraft;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        ReviewDraftRepository $repoReviewDraft
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoReviewDraft = $repoReviewDraft;
    }

    public function showPending()
    {
        $bindings = $this->getBindings('Review drafts');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->reviewsSubpage('Review drafts');

        $bindings['jsInitialSort'] = '[0, "asc"]';

        $bindings['ReviewDraftItems'] = $this->repoReviewDraft->getUnprocessed();

        return view('staff.reviews.review-drafts.list', $bindings);
    }

    public function edit($itemId)
    {
        $bindings = $this->getBindings('Edit review draft');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->reviewsReviewDraftsSubpage('Edit review draft');

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

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        $bindings['ProcessStatusSuccess'] = $this->getServiceReviewFeedItem()->getProcessOptionsSuccess();
        $bindings['ProcessStatusFailure'] = $this->getServiceReviewFeedItem()->getProcessOptionsFailure();

        return view('staff.reviews.review-drafts.edit', $bindings);
    }
}
