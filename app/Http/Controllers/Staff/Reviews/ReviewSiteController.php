<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Models\ReviewSite;

use App\Domain\ViewBindings\Staff as Bindings;
use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Construction\ReviewSite\ReviewSiteBuilder;
use App\Construction\ReviewSite\ReviewSiteDirector;

class ReviewSiteController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|max:100',
        'website_url' => 'required',
        'link_title' => 'required|max:100',
        'rating_scale' => 'required',
    ];

    protected $viewBreadcrumbs;
    protected $viewBindings;
    protected $repoReviewSite;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        Bindings $viewBindings,
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->viewBindings = $viewBindings;
        $this->repoReviewSite = $repoReviewSite;
    }

    public function index()
    {
        $breadcrumbs = $this->viewBreadcrumbs->reviewsSubpage('Review sites');

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff('Review sites');

        $bindings['ReviewSitesActive'] = $this->repoReviewSite->getActive();
        $bindings['ReviewSitesNoRecentReviews'] = $this->repoReviewSite->getNoRecentReviews();

        return view('staff.reviews.review-sites.index', $bindings);
    }

    public function add()
    {
        $breadcrumbs = $this->viewBreadcrumbs->reviewsReviewSitesSubpage('Add review site');

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff('Add review site');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $reviewSiteDirector = new ReviewSiteDirector();
            $reviewSiteBuilder = new ReviewSiteBuilder();
            $reviewSiteDirector->setBuilder($reviewSiteBuilder);
            $reviewSiteDirector->buildNew($request->post());
            $reviewSite = $reviewSiteBuilder->getReviewSite();
            $reviewSite->save();
            $reviewSiteId = $reviewSite->id;

            return redirect(route('staff.reviews.reviewSites.index'));

        }

        $bindings['FormMode'] = 'add';

        $bindings['ReviewImportMethodList'] = [
            ReviewSite::REVIEW_IMPORT_BY_FEED,
            ReviewSite::REVIEW_IMPORT_BY_SCRAPER
        ];

        return view('staff.reviews.review-sites.add', $bindings);
    }

    public function edit(ReviewSite $reviewSite)
    {
        $breadcrumbs = $this->viewBreadcrumbs->reviewsReviewSitesSubpage('Edit review site');

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff('Edit review site');

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $reviewSiteDirector = new ReviewSiteDirector();
            $reviewSiteBuilder = new ReviewSiteBuilder();
            $reviewSiteDirector->setBuilder($reviewSiteBuilder);
            $reviewSiteDirector->buildExisting($reviewSite, $request->post());
            $reviewSite = $reviewSiteBuilder->getReviewSite();
            $reviewSite->save();
            $reviewSiteId = $reviewSite->id;

            return redirect(route('staff.reviews.reviewSites.index'));

        }

        $bindings['FormMode'] = 'edit';
        $bindings['ReviewSiteData'] = $reviewSite;
        $bindings['SiteId'] = $reviewSite->id;

        $bindings['ReviewImportMethodList'] = [
            ReviewSite::REVIEW_IMPORT_BY_FEED,
            ReviewSite::REVIEW_IMPORT_BY_SCRAPER
        ];

        $bindings['StatusList'] = [
            ReviewSite::STATUS_ACTIVE,
            ReviewSite::STATUS_NO_RECENT_REVIEWS
        ];

        return view('staff.reviews.review-sites.edit', $bindings);
    }
}