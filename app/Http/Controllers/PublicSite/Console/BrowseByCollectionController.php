<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use App\Models\Console;

/**
 * Legacy /c/{console}/collection/... URLs redirect to their /browse/collection/... equivalents.
 * The console is deliberately dropped - see BrowseByCategoryController for why.
 */
class BrowseByCollectionController extends Controller
{
    public function landing(Console $console)
    {
        return redirect()->to(route('browse.byCollection.landing'), 301);
    }

    public function page(Console $console, $collection)
    {
        return redirect()->to(route('browse.byCollection.page', ['collection' => $collection]), 301);
    }
}
