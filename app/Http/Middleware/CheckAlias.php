<?php

namespace App\Http\Middleware;

use App\Facades\Settings;
use Closure;

class CheckAlias {
    /**
     * Redirects users without an alias to the dA account linking page,
     * and banned users to the ban page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (Settings::get('is_maintenance_mode') == 1 && !$request->user()->hasPower('maintenance_access')) {
            return redirect('/');
        }
        if (!$request->user()->hasAlias) {
            return redirect('/link');
        }
        if (!$request->user()->hasEmail) {
            return redirect('/email');
        }
        if (!$request->user()->birthday) {
            return redirect('/birthday');
        }
        if (!$request->user()->checkBirthday) {
            return redirect('/blocked');
        }
        if ($request->user()->is_banned) {
            return redirect('/banned');
        }
        if ($request->user()->is_deactivated) {
            return redirect('/deactivated');
        }

        return $next($request);
    }
}
