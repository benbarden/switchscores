<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ListController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function show()
    {
        $bindings = $this->getBindings('News list');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->newsSubpage('News list');

        $bindings['NewsList'] = $this->getServiceNews()->getAll();

        return view('staff.news.list', $bindings);
    }
}