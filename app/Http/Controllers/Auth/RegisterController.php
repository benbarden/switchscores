<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserCreated;
use App\User;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

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
            'display_name' => [
                'required', 'string', 'max:50',
                function($attribute, $value, $fail) {
                    $filteredString = preg_replace('/[^A-Za-z0-9\-\.\_\ \']/', '', $value);
                    if ($value != $filteredString) {
                        return $fail('Please remove special characters from the display name. Spaces, hyphens and underscores are permitted.');
                    }
                },
            ],
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
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
        $user = User::create([
            'display_name' => $data['display_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        event(new UserCreated($user));

        return $user;
    }
}
