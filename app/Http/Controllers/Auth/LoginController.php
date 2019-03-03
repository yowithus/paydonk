<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
// use App\Http\Requests;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  Request  $request
     * @return User
     */
    protected function login(Request $request)
    {
        $this->validate($request, [
            'email'     => 'required|email|max:50', 
            'password'  => 'required|string|min:8',
        ]);

        if (auth()->attempt(array('email' => $request->email, 'password' => $request->password), $request->remember)) {
            if (auth()->user()->role != 'Admin') {
                auth()->logout();
                return back()
                    ->withErrors([ 'error' => 'You are not an admin, please get off!' ])
                    ->withInput();
            }

            return redirect()->intended('/admin');
        } else {
            return back()
                ->withErrors([ 'error' => 'Incorrect email address or password.' ])
                ->withInput();
        }
    }
}
