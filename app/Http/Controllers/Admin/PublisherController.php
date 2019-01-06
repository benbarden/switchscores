<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

class PublisherController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePublisher = $serviceContainer->getPublisherService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Publishers';
        $bindings['PanelTitle'] = 'Publishers';

        $bindings['PublisherList'] = $servicePublisher->getAll();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('admin.publisher.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $servicePublisher = $serviceContainer->getPublisherService();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $publisher = $servicePublisher->create(
                $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.publisher.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Add publisher';
        $bindings['PanelTitle'] = 'Add publisher';
        $bindings['FormMode'] = 'add';

        return view('admin.publisher.add', $bindings);
    }

    public function edit($publisherId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePublisher = $serviceContainer->getPublisherService();

        $publisherData = $servicePublisher->find($publisherId);
        if (!$publisherData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $servicePublisher->edit(
                $publisherData, $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.publisher.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Edit publisher';
        $bindings['PanelTitle'] = 'Edit publisher';
        $bindings['PublisherData'] = $publisherData;
        $bindings['PublisherId'] = $publisherId;

        return view('admin.publisher.edit', $bindings);
    }

    public function delete($publisherId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePublisher = $serviceContainer->getPublisherService();

        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $publisherData = $servicePublisher->find($publisherId);
        if (!$publisherData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the game to be deleted.
        $gamePublishers = $serviceGamePublisher->getByPublisherId($publisherId);
        if (count($gamePublishers) > 0) {
            $customErrors[] = 'Game is linked to '.count($gamePublishers).' publisher(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $servicePublisher->delete($publisherId);

            return redirect(route('admin.publisher.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Delete publisher';
        $bindings['PanelTitle'] = 'Delete publisher';
        $bindings['PublisherData'] = $publisherData;
        $bindings['PublisherId'] = $publisherId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.publisher.delete', $bindings);
    }

}
