<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class ReviewSiteController extends \App\Http\Controllers\BaseController
{
    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|max:50',
        'url' => 'required',
        'feed_url' => 'max:255',
        'link_title' => 'required|max:100',
    ];

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

            $this->validate($request, $this->validationRules);

            $isActive = $request->active == 'on' ? 'Y' : 'N';

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $this->serviceClass->create(
                $request->name, $request->link_title, $request->url, $request->feed_url,
                $isActive, $ratingScale
            );

            return redirect(route('admin.reviews.site.list'));

        }

        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Reviews - Sites - Add site';
        $bindings['PanelTitle'] = 'Add site';
        $bindings['FormMode'] = 'add';

        return view('admin.reviews.site.add', $bindings);
    }

    public function edit($siteId)
    {
        $reviewSiteData = $this->serviceClass->find($siteId);
        if (!$reviewSiteData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $isActive = $request->active == 'on' ? 'Y' : 'N';

            if (isset($request->rating_scale)) {
                $ratingScale = $request->rating_scale;
            } else {
                $ratingScale = 10;
            }

            $this->serviceClass->edit(
                $reviewSiteData,
                $request->name, $request->link_title, $request->url, $request->feed_url,
                $isActive, $ratingScale
            );

            return redirect(route('admin.reviews.site.list'));

        }

        // ADD REVIEW SITE
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Reviews - Sites - Edit site';
        $bindings['PanelTitle'] = 'Edit site';
        $bindings['ReviewSiteData'] = $reviewSiteData;
        $bindings['SiteId'] = $siteId;

        $bindings['FormMode'] = 'edit';

        return view('admin.reviews.site.edit', $bindings);
    }
}
