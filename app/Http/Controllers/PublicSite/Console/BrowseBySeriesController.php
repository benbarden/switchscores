<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use App\Models\Console;

class BrowseBySeriesController extends Controller
{
    public function landing(Console $console)
    {
        return redirect()->to(route('browse.bySeries.landing') . '?console=' . $console->slug, 301);
    }

    public function page(Console $console, $series)
    {
        return redirect()->to(route('browse.bySeries.page', ['series' => $series]) . '?console=' . $console->slug, 301);
    }
}
