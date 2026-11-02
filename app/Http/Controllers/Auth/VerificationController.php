<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse;
use Laravel\Fortify\Http\Responses\RedirectAsIntended;
use Throwable;

class VerificationController extends Controller {
    /**
     * Resend the email verification notification.
     *
     * @return mixed
     */
    public function resend(Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse('', 204)
                : app(RedirectAsIntended::class, ['name' => 'email-verification']);
        }

        try {
            $request->user()->sendEmailVerificationNotification();

            return app(EmailVerificationNotificationSentResponse::class);
        } catch (Throwable $e) {
            report($e);

            Log::error('Failed to send email verification notification during resend.', [
                'user_id' => $request->user()->id,
                'email'   => $request->user()->email,
                'error'   => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return new JsonResponse(['message' => 'We couldn\'t send the verification email due to email configuration issues. Please contact an administrator.'], 500);
            }

            return back()->with('error', 'We couldn\'t send the verification email due to email configuration issues. Please contact an administrator.');
        }
    }
}
