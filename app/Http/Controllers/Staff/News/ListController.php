<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class ListController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $serviceNews = $this->getServiceNews();

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - News list';
        $bindings['PageTitle'] = 'News list';

        $newsList = $serviceNews->getAll();

        $bindings['NewsList'] = $newsList;

        return view('staff.news.list', $bindings);
    }
}