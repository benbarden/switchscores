<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use App\Models\Console;

class BrowseByCollectionController extends Controller
{
    public function landing(Console $console)
    {
        return redirect()->to(route('browse.byCollection.landing') . '?console=' . $console->slug, 301);
    }

    public function page(Console $console, $collection)
    {
        return redirect()->to(route('browse.byCollection.page', ['collection' => $collection]) . '?console=' . $console->slug, 301);
    }
}
