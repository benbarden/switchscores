<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\News\Repository as RepoNews;

class ListController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private RepoNews $repoNews
    )
    {
    }

    public function show()
    {
        $pageTitle = 'News list';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::newsSubpage($pageTitle))->bindings;

        $bindings['NewsList'] = $this->repoNews->getAll();

        return view('staff.news.list', $bindings);
    }
}