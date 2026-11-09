<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeEmails {
    /**
     * Hotfix for security advisory GHSA-5vg9-5847-vvmq
     * https://github.com/laravel/framework/security/advisories/GHSA-5vg9-5847-vvmq
     * Will no longer be necessary on laravel/framework ^12.60.0 or ^13.10.0.
     *
     * FILTER_SANITIZE_EMAIL will remove vulnerable \ and / characters
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        if ($request->has('email')) {
            $request->merge([
                'email' => filter_var($request->email, FILTER_SANITIZE_EMAIL),
            ]);
        }

        return $next($request);
    }
}
