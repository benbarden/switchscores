<?php

namespace App\Http\Controllers\Members\Developers;

use App\Domain\PersonalAccessToken\Repo as PersonalAccessTokenRepo;
use App\Models\PersonalAccessToken;
use Illuminate\Routing\Controller as Controller;


class ApiController extends Controller
{
    public function __construct(
        private PersonalAccessTokenRepo $repoPersonalAccessToken
    )
    {
    }

    public function guide()
    {
        $pageTitle = 'API guide';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->developersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        return view('members.developers.api.guide', $bindings);
    }

    public function methods()
    {
        $pageTitle = 'API methods';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->developersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        return view('members.developers.api.methods', $bindings);
    }

    public function tokens()
    {
        $pageTitle = 'API tokens';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->developersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $bindings['UserTokenList'] = $this->repoPersonalAccessToken->getByTokenableId($userId);

        return view('members.developers.api.tokens', $bindings);
    }

    public function createToken()
    {
        $pageTitle = 'Create API token';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->developersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();

        $token = $currentUser->createToken(PersonalAccessToken::API_GET_GAMES, ['games:list']);
        $bindings['TokenValue'] = $token->plainTextToken;

        return view('members.developers.api.create-token', $bindings);
    }

    public function deleteToken($tokenId)
    {
        $pageTitle = 'Delete API token';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->developersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();

        $tokenToDelete = $this->repoPersonalAccessToken->find($tokenId);
        if (!$tokenToDelete) abort(404);
        if ($tokenToDelete->tokenable_id != $currentUser->id) abort(403);

        $this->repoPersonalAccessToken->delete($tokenId);

        return view('members.developers.api.delete-token', $bindings);
    }
}
