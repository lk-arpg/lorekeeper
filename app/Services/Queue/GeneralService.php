<?php

namespace App\Services\Queue;

use App\Models\Character\Character;
use App\Services\Service;
use DB;

class GeneralService extends Service {
    /**
     * Validate the character ownership.
     *
     * @param mixed $slug
     * @param mixed $user
     */
    public function checkCharacterOwnership($slug, $user) {
        DB::beginTransaction();

        try {
            $character = Character::where('slug', $slug)->first();
            if (!$character) {
                throw new \Exception('Please enter a valid character code.');
            }
            if ($character->user_id != $user->id) {
                throw new \Exception('That character does not belong to you.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
