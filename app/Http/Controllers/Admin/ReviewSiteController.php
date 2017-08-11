<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class ReviewSiteController extends \App\Http\Controllers\BaseController
{
    /**
     * @var \App\Services\ReviewSiteService
     */
    private $serviceClass;

    public function __construct()
    {
        $this->serviceClass = resolve('Services\ReviewSiteService');
        parent::__construct();
    }

    public function showList()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Reviews - Sites';
        $bindings['PanelTitle'] = 'Reviews: Sites';

        $reviewSites = $this->serviceClass->getAll();

        $bindings['ReviewSites'] = $reviewSites;

        return view('admin.reviews.site.list', $bindings);
    }

    public function add()
    {
        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, [
                'name' => 'required|max:50',
                'url' => 'required',
                'active' => 'required'
            ]);

            $isActive = $request->active == 'on' ? 'Y' : 'N';

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $this->serviceClass->create(
                $request->name, $request->url, $isActive, $ratingScale
            );

            return redirect(route('admin.reviews.site.list'));

        }

        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Reviews - Sites - Add site';
        $bindings['PanelTitle'] = 'Add site';

        return view('admin.reviews.site.add', $bindings);
    }
}
