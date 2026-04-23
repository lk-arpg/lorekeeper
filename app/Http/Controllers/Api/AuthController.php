<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
    /**
     * Authenticate with email/password and receive a PAT through the API.
     */
    public function postGenerateToken(Request $request) {
        if (!Settings::get('allow_token_generation_via_api')) {
            return response()->json([
                'message' => 'Token generation via API is not allowed.',
            ], 403);
        }

        $request->validate([
            'email'      => 'required|email',
            'password'   => 'required',
            'token_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete any pre-existing tokens
        $user->tokens()->delete();

        return $user->createToken($request->token_name)->plainTextToken;
    }
}
