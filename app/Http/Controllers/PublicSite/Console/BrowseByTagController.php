<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use App\Models\Console;

/**
 * Legacy /c/{console}/tag/... URLs redirect to their /browse/tag/... equivalents.
 * The console is deliberately dropped - see BrowseByCategoryController for why.
 */
class BrowseByTagController extends Controller
{
    public function landing(Console $console)
    {
        return redirect()->to(route('browse.byTag.landing'), 301);
    }

    public function page(Console $console, $tag)
    {
        return redirect()->to(route('browse.byTag.page', ['tag' => $tag]), 301);
    }
}
