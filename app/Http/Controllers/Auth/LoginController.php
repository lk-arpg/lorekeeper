<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\User\UserAlias;
use App\Services\LinkService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller {
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

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Authenticate via Aliases.
     *
     * @param mixed $provider
     *
     * @return \Illuminate\Http\Response
     */
    public function getAuthRedirect(LinkService $service, $provider) {
        $result = $service->getAuthRedirect($provider, true);

        return $result;
    }

    /**
     * Authenticate via Aliases.
     *
     * @param mixed $provider
     *
     * @return \Illuminate\Http\Response
     */
    public function getAuthCallback(LinkService $service, $provider) {
        // Toyhouse runs on Laravel Passport for OAuth2 and this has some issues with state exceptions,
        // admin suggested the easy fix (to use stateless)
        $socialite = $provider == 'toyhouse' ? Socialite::driver($provider)->stateless() : Socialite::driver($provider);
        // Needs to match for the user call to work
        $socialite->redirectUrl(str_replace('auth', 'login', url(config('services.'.$provider.'.redirect'))));
        $result = $socialite->user();

        $user = UserAlias::where('site', $provider)->where('user_snowflake', $result->id)->first();
        if (!$user) {
            return redirect('/register/'.$provider)->with(['userData' => $result]);
        }

        Auth::login($user->user, true);

        return redirect($this->redirectTo);
    }
}
