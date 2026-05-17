<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;
use App\Models\Console;

class BrowseByCategoryController extends Controller
{
    public function landing(Console $console)
    {
        return redirect()->to(route('browse.byCategory.landing') . '?console=' . $console->slug, 301);
    }

    public function page(Console $console, $category)
    {
        return redirect()->to(route('browse.byCategory.page', ['category' => $category]) . '?console=' . $console->slug, 301);
    }

    public function list(Request $request, Console $console, $category)
    {
        return redirect()->to(route('browse.byCategory.list', ['category' => $category]) . '?console=' . $console->slug, 301);
    }
}
