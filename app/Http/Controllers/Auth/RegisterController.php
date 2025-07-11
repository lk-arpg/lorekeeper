<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User\User;
use App\Models\User\UserAlias;
use App\Services\InvitationService;
use App\Services\LinkService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class RegisterController extends Controller {
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

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @param mixed $provider
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegisterWithDriver($provider) {
        $userData = session()->get('userData');

        return view('auth.register_with_driver', [
            'userCount' => User::count(),
            'provider'  => $provider,
            'user'      => $userData->nickname ?? null,
            'token'     => $userData->token ?? null,
        ]);
    }

    /**
     * Show the application registration form.
     *
     * @param mixed $provider
     *
     * @return \Illuminate\Http\Response
     */
    public function postRegisterWithDriver(LinkService $service, Request $request, $provider) {
        $providerData = Socialite::driver($provider)->userFromToken($request->get('token'));

        if (UserAlias::where('site', $provider)->where('user_snowflake', $providerData->id)->first()) {
            flash('An Account is already tied to the authorized '.$provider.' account.')->error();

            return redirect()->back();
        }

        $data = $request->all();

        (new UserService)->validator($data, true)->validate();
        $user = $this->create($data);
        if ($service->saveProvider($provider, $providerData, $user)) {
            Auth::login($user);

            return redirect('/');
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return User
     */
    protected function create(array $data) {
        DB::beginTransaction();
        $service = new UserService;
        $user = $service->createUser(Arr::only($data, ['name', 'email', 'password', 'dob']));
        if (!Settings::get('is_registration_open')) {
            (new InvitationService)->useInvitation(Invitation::where('code', $data['code'])->first(), $user);
        }
        DB::commit();

        return $user;
    }
}
