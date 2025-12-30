<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Models\ReviewSite;

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

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private ReviewSiteRepository $repoReviewSite
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Review sites';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsSubpage($pageTitle))->bindings;

        $bindings['ReviewSitesActive'] = $this->repoReviewSite->getActive();
        $bindings['ReviewSitesNoRecentReviews'] = $this->repoReviewSite->getNoRecentReviews();
        $bindings['ReviewSitesArchived'] = $this->repoReviewSite->getArchived();

        return view('staff.reviews.review-sites.index', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add review site';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsReviewSitesSubpage($pageTitle))->bindings;

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
        $pageTitle = 'Edit review site';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsReviewSitesSubpage($pageTitle))->bindings;

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
            ReviewSite::STATUS_NO_RECENT_REVIEWS,
            ReviewSite::STATUS_ARCHIVED,
        ];

        return view('staff.reviews.review-sites.edit', $bindings);
    }
}