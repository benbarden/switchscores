<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Traits\AuthUser;
use App\Traits\SwitchServices;

class CategoryController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function showList()
    {
        $serviceCategory = $this->getServiceCategory();

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Categories';
        $bindings['PageTitle'] = 'Categories';

        $bindings['CategoryList'] = $serviceCategory->getAll();

        return view('staff.categorisation.category.list', $bindings);
    }

    public function addCategory()
    {
        $serviceCategory = $this->getServiceCategory();
        $serviceUser = $this->getServiceUser();
        $serviceUrl = $this->getServiceUrl();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $categoryName = $request->categoryName;
        if (!$categoryName) {
            return response()->json(['error' => 'Missing data: categoryName'], 400);
        }

        $existingRecord = $serviceCategory->getByName($categoryName);
        if ($existingRecord) {
            return response()->json(['error' => 'Category already exists!'], 400);
        }

        $linkTitle = $serviceUrl->generateLinkText($categoryName);

        $serviceCategory->create($categoryName, $linkTitle);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}