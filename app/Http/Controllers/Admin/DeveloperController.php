<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

class DeveloperController extends Controller
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

        $serviceDeveloper = $serviceContainer->getDeveloperService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Developers';
        $bindings['PanelTitle'] = 'Developers';

        $bindings['DeveloperList'] = $serviceDeveloper->getAll();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('admin.developer.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $serviceDeveloper = $serviceContainer->getDeveloperService();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $developer = $serviceDeveloper->create(
                $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.developer.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Add developer';
        $bindings['PanelTitle'] = 'Add developer';
        $bindings['FormMode'] = 'add';

        return view('admin.developer.add', $bindings);
    }

    public function edit($developerId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceDeveloper = $serviceContainer->getDeveloperService();

        $developerData = $serviceDeveloper->find($developerId);
        if (!$developerData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $serviceDeveloper->edit(
                $developerData, $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.developer.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Edit developer';
        $bindings['PanelTitle'] = 'Edit developer';
        $bindings['DeveloperData'] = $developerData;
        $bindings['DeveloperId'] = $developerId;

        return view('admin.developer.edit', $bindings);
    }

    public function delete($developerId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceDeveloper = $serviceContainer->getDeveloperService();

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $developerData = $serviceDeveloper->find($developerId);
        if (!$developerData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the game to be deleted.
        $gameDevelopers = $serviceGameDeveloper->getByDeveloperId($developerId);
        if (count($gameDevelopers) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameDevelopers).' developer(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceDeveloper->delete($developerId);

            return redirect(route('admin.developer.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Delete developer';
        $bindings['PanelTitle'] = 'Delete developer';
        $bindings['DeveloperData'] = $developerData;
        $bindings['DeveloperId'] = $developerId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.developer.delete', $bindings);
    }
}
