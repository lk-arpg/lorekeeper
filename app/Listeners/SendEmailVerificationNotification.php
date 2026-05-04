<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Throwable;

class SendEmailVerificationNotification {
    /**
     * Handle the event.
     */
    public function handle(Registered $event) {
        if ($event->user instanceof MustVerifyEmail && !$event->user->hasVerifiedEmail()) {
            try {
                $event->user->sendEmailVerificationNotification();
            } catch (Throwable $e) {
                flash('Account created successfully! However, we couldn\'t send the verification email due to email configuration issues. Please contact an administrator to verify your email manually.')->warning();
            }
        }
    }
}
