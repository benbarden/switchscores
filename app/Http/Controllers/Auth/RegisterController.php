<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserCreated;
use App\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\View;

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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        abort(404);
        /*
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

        $user = User::create($values);

        event(new UserCreated($user));

        return $user;
        */
    }
}
