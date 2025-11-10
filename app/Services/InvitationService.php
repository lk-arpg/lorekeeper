<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Facades\DB;

class InvitationService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Invitation Service
    |--------------------------------------------------------------------------
    |
    | Handles creation and usage of site registration invitation codes.
    |
    */

    /**
     * Generates an invitation code, saving the user who generated it.
     *
     * @param \App\Models\User\User $user
     *
     * @return bool|Invitation
     */
    public function generateInvitation($user) {
        DB::beginTransaction();

        try {
            $invitation = Invitation::create([
                'code'    => $this->generateCode(),
                'user_id' => $user->id,
            ]);

            return $this->commitReturn($invitation);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Marks an invitation code as used, saving the user who used it.
     *
     * @param \App\Models\User\User $user
     * @param mixed                 $invitation
     *
     * @return bool|Invitation
     */
    public function useInvitation($invitation, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if ($invitation->recipient_id) {
                throw new \Exception('This invitation key has already been used.');
            }

            $invitation->recipient_id = $user->id;
            $invitation->save();

            return $this->commitReturn($invitation);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an unused invitation code.
     *
     * @param Invitation $invitation
     *
     * @return bool
     */
    public function deleteInvitation($invitation) {
        DB::beginTransaction();

        try {
            // Check first if the invitation has been used
            if ($invitation->recipient_id) {
                throw new \Exception('This invitation has already been used.');
            }
            $invitation->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Generates a string for an invitation code.
     *
     * @return string
     */
    private function generateCode() {
        return randomString(10);
    }
}
