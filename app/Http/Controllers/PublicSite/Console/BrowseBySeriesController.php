<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use App\Models\Console;

/**
 * Legacy /c/{console}/series/... URLs redirect to their /browse/series/... equivalents.
 * The console is deliberately dropped - see BrowseByCategoryController for why.
 */
class BrowseBySeriesController extends Controller
{
    public function landing(Console $console)
    {
        return redirect()->to(route('browse.bySeries.landing'), 301);
    }

    public function page(Console $console, $series)
    {
        return redirect()->to(route('browse.bySeries.page', ['series' => $series]), 301);
    }
}
