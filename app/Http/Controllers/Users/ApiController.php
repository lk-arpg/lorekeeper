<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Api Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of API access.
    |
    */

    /**
     * Generates (or re-generates) an API token.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postGenerateToken(Request $request, UserService $service) {
        if (!Settings::get('allow_users_to_generate_tokens')) {
            abort(404);
        }

        if (!$service->generateToken(Auth::user())) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back()->withInput();
    }

    /**
     * Generates (or re-generates) an API token.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postRevokeToken(Request $request, UserService $service) {
        if (!$service->revokeTokens(Auth::user())) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('Token(s) revoked successfully.')->success();
        }

        return redirect()->back()->withInput();
    }

    /**
     * Generates (or re-generates) an API token without CSRF protection.
     * This token is different from the one provided to the user via settings.
     *
     * @return mixed
     */
    public function getGenerateToken(Request $request, UserService $service) {
        return $service->generateTokenAPI();
    }
}
