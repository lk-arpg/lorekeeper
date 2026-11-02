<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PostRequestThrottleMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response {
        $allowedRoutes = [
            'admin/*',
        ];
        if ($request->isMethod('get') || $request->is(...$allowedRoutes) || app()->environment('local')) {
            return $next($request);
        }

        $key = $request->user()?->id ?: $request->ip();
        $key .= $request->fullUrl(); // add current url to key to prevent rate limiting on different pages
        $maxAttempts = 1;
        $decaySeconds = 10;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            flash('Too many requests - please try again later.')->error()->important();
            flash('Your initial action has likely been performed successfully. Please check to ensure this is the case before trying again.')->success()->important();

            if ($request->user() && config('lorekeeper.settings.site_logging_webhook')) {
                $webhookCooldown = 120;
                $cacheKey = 'webhook_sent_'.$key;
                if (!Cache::has($cacheKey)) {
                    Cache::put($cacheKey, true, $webhookCooldown);
                    $this->sendThrottleLogWebhook($request);
                }
            } else {
                Log::channel('throttle')->info('Rate limited user', ['url' => $request->fullUrl(), 'user' => $request->user()?->name ?: $request->ip()]);
            }

            // If the response is from ajax it's not expecting a full redirect with the entire site bundled in as a response
            return $request->ajax() ?
                response("<div class='alert alert-danger mb-2'>Too many requests - please try again later.</div><div class='alert alert-success'>Your initial action has likely been performed successfully. Please check to ensure this is the case before trying again.</div>")
                : redirect()->back();
        }

        RateLimiter::hit($key, $decaySeconds);

        return $next($request);
    }

    /**
     * Sends a log to the site administrators that a user has been rate limited.
     */
    private function sendThrottleLogWebhook(Request $request): void {
        $webhook = config('lorekeeper.settings.site_logging_webhook');
        $data = [];

        $author_data = [
            'name'     => $request->user()->name,
            'url'      => $request->user()->url,
            'icon_url' => $request->user()->avatarUrl,
        ];
        $data['username'] = config('lorekeeper.settings.site_name', 'Lorekeeper');
        $data['avatar_url'] = url('favicon.ico');
        $data['content'] = 'A user has been rate limited, url: '.$request->fullUrl();
        $data['embeds'] = [[
            'color'       => 6208428,
            'author'      => $author_data ?? null,
            'title'       => 'Rate Limited User',
            'description' => '',
        ]];

        $ch = curl_init($webhook);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}
