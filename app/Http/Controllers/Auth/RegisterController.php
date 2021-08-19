<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\View;

use App\Events\UserCreated;
use App\User;

use App\Domain\InviteCode\Repository as InviteCodeRepository;
use App\Domain\InviteCode\CodeRedemption as InviteCodeRedemption;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    private $repoInviteCode;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        InviteCodeRepository $repoInviteCode
    )
    {
        $this->repoInviteCode = $repoInviteCode;

        $this->middleware('guest');
        View::share('PageTitle', 'Register');
        View::share('TopTitle', 'Register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'signup_name' => [
                'required', 'string', 'max:50',
                function($attribute, $value, $fail) {
                    $filteredString = preg_replace('/[^A-Za-z0-9\-\.\_\ \']/', '', $value);
                    if ($value != $filteredString) {
                        return $fail('Please remove special characters from the display name. Spaces, hyphens and underscores are permitted.');
                    }
                },
            ],
            'signup_email' => 'required|string|email|min:6|max:100|unique:users,email',
            'signup_pass' => 'required|string|min:6|confirmed',
            'invite_code' => [
                'required', 'string',
                function($attribute, $value, $fail) {
                    $inviteCode = $this->repoInviteCode->getByCode($value);
                    if (!$inviteCode) {
                        return $fail('Invalid invite code.');
                    } else {
                        if ($inviteCode->is_active == 0) {
                            return $fail('Invalid invite code.');
                        } elseif ($inviteCode->times_left == 0) {
                            return $fail('Invalid invite code.');
                        }
                    }
                }
            ]
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $values = [];
        if (array_key_exists('signup_name', $data)) {
            $values['display_name'] = $data['signup_name'];
        }
        if (array_key_exists('signup_email', $data)) {
            $values['email'] = $data['signup_email'];
        }
        if (array_key_exists('signup_pass', $data)) {
            $values['password'] = $data['signup_pass'];
        }
        if (array_key_exists('signup_alpha', $data)) {
            $values['signup_alpha'] = $data['signup_alpha'];
        }
        if (array_key_exists('signup_beta', $data)) {
            $values['signup_beta'] = $data['signup_beta'];
        }
        if (array_key_exists('invite_code', $data)) {
            $inviteCode = $this->repoInviteCode->getByCode($data['invite_code']);
            $values['invite_code_id'] = $inviteCode->id;
            $hasInviteCode = true;
            if ($inviteCode->games_company_id) {
                $values['games_company_id'] = $inviteCode->games_company_id;
                $this->redirectTo = route('games-companies.index').'?action=newsignup';
            } elseif ($inviteCode->reviewer_id) {
                $values['partner_id'] = $inviteCode->reviewer_id;
                $this->redirectTo = route('reviewers.index').'?action=newsignup';
            }
        } else {
            $hasInviteCode = false;
        }

        $user = User::create($values);

        if ($hasInviteCode) {
            $redeemInviteCode = new InviteCodeRedemption($inviteCode);
            $redeemInviteCode->redeemOnce();
        }

        event(new UserCreated($user));

        return $user;
    }
}
