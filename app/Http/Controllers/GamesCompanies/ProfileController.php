<?php

namespace App\Http\Controllers\GamesCompanies;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;

class ProfileController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $repoGamesCompany;

    /**
     * @var array
     */
    private $validationRules = [
        //'contact_name' => 'max:100',
    ];

    public function __construct(
        GamesCompanyRepository $repoGamesCompany
    )
    {
        $this->repoGamesCompany = $repoGamesCompany;
    }

    public function edit()
    {
        $currentUser = resolve('User/Repository')->currentUser();

        $gamesCompany = $currentUser->gamesCompany;
        // These shouldn't be possible but it saves problems later on
        if (!$gamesCompany) abort(403);

        $gamesCompanyId = $gamesCompany->id;

        $bindings = [];
        $request = request();

        if ($request->isMethod('post')) {

            // Run initial validation rules
            $validator = Validator::make($request->all(), $this->validationRules);
            if ($validator->fails()) {
                return redirect(route('games-companies.profile.edit'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // OK to proceed
            $email = $request->email;
            $websiteUrl = $request->website_url;
            $twitterId = $request->twitter_id;
            $threadsId = $request->threads_id;
            $blueskyId = $request->bluesky_id;

            $gamesCompany->email = $email;
            $gamesCompany->website_url = $websiteUrl;
            $gamesCompany->twitter_id = $twitterId;
            $gamesCompany->threads_id = $threadsId;
            $gamesCompany->bluesky_id = $blueskyId;
            $gamesCompany->save();

            return redirect(route('games-companies.index'));
        } else {
            $bindings['FormMode'] = 'edit';
        }

        $bindings['TopTitle'] = 'Edit profile';
        $bindings['PageTitle'] = 'Edit profile';

        $bindings['GamesCompany'] = $gamesCompany;

        return view('games-companies.profile.edit', $bindings);
    }
}
