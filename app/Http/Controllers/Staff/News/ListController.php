<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;

use App\Domain\News\Repository as RepoNews;

class ListController extends Controller
{
    public function __construct(
        private RepoNews $repoNews
    )
    {
    }

    public function show()
    {
        $pageTitle = 'News list';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['NewsList'] = $this->repoNews->getAll();

        return view('staff.news.list', $bindings);
    }
}