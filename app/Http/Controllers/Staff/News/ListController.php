<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ListController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsNewsSubpage('News list');

        $bindings['NewsList'] = $this->getServiceNews()->getAll();

        return view('staff.news.list', $bindings);
    }
}