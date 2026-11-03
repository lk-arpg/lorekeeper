<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class ExtensionService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Extension Service
    |--------------------------------------------------------------------------
    |
    | Handles functions relating to extensions.
    |
    */

    /**
     * Updates existing notifications in the database.
     * Part of the project to move each author's works to
     * using a distinct notifications prefix.
     * Should be called with a command instructing it
     * in what notifications to move where.
     *
     * @param mixed $source
     * @param mixed $destination
     *
     * @return bool
     */
    public function updateNotifications($source, $destination) {
        $count = Notification::where('notification_type_id', $source)->count();
        if ($count && isset($destination)) {
            DB::beginTransaction();
            try {
                Notification::where('notification_type_id', $source)->update(['notification_type_id' => $destination]);

                return $this->commitReturn(true);
            } catch (\Exception $e) {
                $this->setError('error', $e->getMessage());
            }

            return $this->rollbackReturn(false);
        } else {
            return true;
        }
    }
}
