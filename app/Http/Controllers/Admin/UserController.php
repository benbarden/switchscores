<?php

namespace App\Http\Controllers\Admin;

use App\UserRole;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

class UserController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'display_name' => 'required',
        //'email' => 'required',
    ];

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userService = $serviceContainer->getUserService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Users';
        $bindings['PageTitle'] = 'Users';

        $userList = $userService->getAll();

        $bindings['UserList'] = $userList;

        return view('admin.user.list', $bindings);
    }

    public function showUser($userId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userService = $serviceContainer->getUserService();
        $collectionService = $serviceContainer->getUserGamesCollectionService();

        $userData = $userService->find($userId);
        if (!$userData) abort(404);

        $displayName = $userData->display_name;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - View user - '.$displayName;
        $bindings['PageTitle'] = 'View user - '.$displayName;

        $bindings['UserData'] = $userData;

        $bindings['CollectionList'] = $collectionService->getByUser($userId);
        $bindings['CollectionStats'] = $collectionService->getStats($userId);

        return view('admin.user.view', $bindings);
    }

    public function editUser($userId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceUser = $serviceContainer->getUserService();
        $servicePartner = $serviceContainer->getPartnerService();

        $userData = $serviceUser->find($userId);
        if (!$userData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $displayName = $request->display_name;
            $email = $request->email;
            $twitterUserId = $request->twitter_user_id;
            $partnerId = $request->partner_id;
            $isStaff = $request->is_staff;

            $serviceUser->edit($userData, $displayName, $email, $partnerId, $twitterUserId, $isStaff);

            // Clear roles
            $userData->setRoles([]);

            if (isset($request->role_item)) {

                foreach ($request->role_item as $roleKey => $roleValue) {

                    $role = UserRole::getRoleFromId($roleKey);
                    $userData->addRole($role);

                }
                $userData->save();

            }

            return redirect(route('admin.user.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Edit user';
        $bindings['PageTitle'] = 'Edit user';
        $bindings['UserData'] = $userData;
        $bindings['UserId'] = $userId;

        $bindings['PartnerList'] = $servicePartner->getAllForUserAssignment();

        $bindings['RoleList'] = UserRole::getRoleList();

        $userRoleList = $userData->user_roles;
        if ($userRoleList) {
            $userRoleListForView = [];
            foreach ($userRoleList as $userRole) {
                $roleId = UserRole::getIdFromName($userRole);
                $userRoleListForView[] = ['id' => $roleId, 'role' => $userRole];
            }
            $bindings['UserRoleList'] = $userRoleListForView;
        }

        return view('admin.user.edit', $bindings);
    }

    public function deleteUser($userId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        // Core
        $serviceUser = $serviceContainer->getUserService();

        // Validation
        $serviceGameChangeHistory = $serviceContainer->getGameChangeHistoryService();
        $servicePartnerReview = $serviceContainer->getPartnerReviewService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceReviewUser = $serviceContainer->getReviewUserService();

        // Deletion
        $serviceUserGamesCollection = $serviceContainer->getUserGamesCollectionService();
        $serviceUserListItem = $serviceContainer->getUserListItemService();
        $serviceUserList = $serviceContainer->getUserListService();

        $userData = $serviceUser->find($userId);
        if (!$userData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the user to be deleted.
        $gameChangeHistory = $serviceGameChangeHistory->getByUserId($userId);
        if (count($gameChangeHistory) > 0) {
            $customErrors[] = 'User has authored '.count($gameChangeHistory).' game change history record(s) and cannot be deleted';
        }
        $partnerReviews = $servicePartnerReview->getByUser($userId);
        if (count($partnerReviews) > 0) {
            $customErrors[] = 'User has created '.count($partnerReviews).' partner review(s)';
        }
        $reviewLinks = $serviceReviewLink->getByUser($userId);
        if (count($reviewLinks) > 0) {
            $customErrors[] = 'User has created '.count($reviewLinks).' review link(s)';
        }
        $quickReviews = $serviceReviewUser->getAllByUser($userId);
        if (count($quickReviews) > 0) {
            $customErrors[] = 'User has created '.count($quickReviews).' quick review(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceUserGamesCollection->deleteByUserId($userId);
            $userLists = $serviceUserList->getAllByUser($userId);
            if ($userLists) {
                foreach ($userLists as $list) {
                    $listId = $list->id;
                    $serviceUserListItem->deleteByList($listId);
                    $serviceUserList->delete($listId);
                }
            }
            $serviceUser->deleteUser($userId);

            // Done

            return redirect(route('admin.user.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Users - Delete user';
        $bindings['PageTitle'] = 'Delete user';
        $bindings['UserData'] = $userData;
        $bindings['UserId'] = $userId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.user.delete', $bindings);
    }

}