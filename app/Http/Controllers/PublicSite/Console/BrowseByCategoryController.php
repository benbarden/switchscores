<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;
use App\Models\Console;

/**
 * Legacy /c/{console}/category/... URLs redirect to their /browse/category/... equivalents.
 *
 * These deliberately drop the console, rather than passing it through as ?console={slug}.
 * A ?console= URL declares the console-agnostic page as its canonical, so appending the
 * param sent Google on a two-hop journey (301 to the variant, then canonical back to the
 * console-agnostic page) and minted an "alternative page with proper canonical tag" URL on
 * every legacy hit. Redirecting straight to the canonical is a single hop to the same
 * destination Google already ended up at.
 */
class BrowseByCategoryController extends Controller
{
    public function landing(Console $console)
    {
        return redirect()->to(route('browse.byCategory.landing'), 301);
    }

    public function page(Console $console, $category)
    {
        return redirect()->to(route('browse.byCategory.page', ['category' => $category]), 301);
    }

    public function list(Request $request, Console $console, $category)
    {
        return redirect()->to(route('browse.byCategory.list', ['category' => $category]), 301);
    }
}
