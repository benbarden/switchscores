<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use App\Models\Console;

class BrowseByTagController extends Controller
{
    public function landing(Console $console)
    {
        return redirect()->to(route('browse.byTag.landing') . '?console=' . $console->slug, 301);
    }

    public function page(Console $console, $tag)
    {
        return redirect()->to(route('browse.byTag.page', ['tag' => $tag]) . '?console=' . $console->slug, 301);
    }
}
