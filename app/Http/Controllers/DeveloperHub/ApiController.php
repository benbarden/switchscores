<?php

namespace App\Http\Controllers\DeveloperHub;

use Illuminate\Routing\Controller as Controller;

use App\Domain\PersonalAccessToken\Repo as PersonalAccessTokenRepo;

use App\Models\PersonalAccessToken;


class ApiController extends Controller
{
    private $repoPersonalAccessToken;
    public function __construct(
        PersonalAccessTokenRepo $repoPersonalAccessToken
    )
    {
        $this->repoPersonalAccessToken = $repoPersonalAccessToken;
    }

    public function guide()
    {
        $bindings = [];

        $pageTitle = 'API guide';

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('developer-hub.api.guide', $bindings);
    }

    public function methods()
    {
        $bindings = [];

        $pageTitle = 'API methods';

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('developer-hub.api.methods', $bindings);
    }

    public function tokens()
    {
        $bindings = [];

        $pageTitle = 'API tokens';

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $bindings['UserTokenList'] = $this->repoPersonalAccessToken->getByTokenableId($userId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('developer-hub.api.tokens', $bindings);
    }

    public function createToken()
    {
        $bindings = [];

        $pageTitle = 'Create API token';

        $currentUser = resolve('User/Repository')->currentUser();

        $token = $currentUser->createToken(PersonalAccessToken::API_GET_GAMES, ['games:list']);
        $bindings['TokenValue'] = $token->plainTextToken;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('developer-hub.api.create-token', $bindings);
    }

    public function deleteToken($tokenId)
    {
        $bindings = [];

        $pageTitle = 'Delete API token';

        $currentUser = resolve('User/Repository')->currentUser();

        $tokenToDelete = $this->repoPersonalAccessToken->find($tokenId);
        if (!$tokenToDelete) abort(404);
        if ($tokenToDelete->tokenable_id != $currentUser->id) abort(403);

        $this->repoPersonalAccessToken->delete($tokenId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('developer-hub.api.delete-token', $bindings);
    }
}
