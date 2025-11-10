<?php

namespace App\Helpers;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class Notifications {
    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Creates user notifications.
    |
    */

    /**
     * Creates a new notification.
     *
     * @param string                $type
     * @param \App\Models\User\User $user
     * @param array                 $data
     *
     * @return bool
     */
    public function create($type, $user, $data) {
        DB::beginTransaction();

        try {
            $notification = Notification::create([
                'user_id'               => $user->id,
                'notification_type_id'  => Notification::getNotificationId($type),
                'data'                  => $data,
                'is_unread'             => 1,
            ]);

            $user->notifications_unread++;
            $user->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        DB::rollback();

        return false;
    }
}
