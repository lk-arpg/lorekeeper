<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RewardManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Reward Service
    |--------------------------------------------------------------------------
    |
    | Handles the grants of rewards on objects.
    |
    */

    /**********************************************************************************************

        REWARD GRANTS

    **********************************************************************************************/

    /**
     * Grants rewards to users or characters.
     *
     * @param mixed $data
     * @param mixed $type
     * @param mixed $staff
     *
     * @return bool
     */
    public function grantRewards($data, $staff, $type = 'user') {
        DB::beginTransaction();

        try {
            $assets = null;
            if ($type == 'user') {
                $assets = createAssetsArray();
            } else {
                $assets = createAssetsArray(true);
            }

            if (!isset($data['rewardable_type']) || !isset($data['rewardable_id']) || !isset($data['quantity'])) {
                throw new \Exception('You must select at least one reward to grant.');
            }

            foreach ($data['quantity'] as $q) {
                if ($q <= 0) {
                    throw new \Exception('All quantities must be at least 1.');
                }
            }

            // add assets first
            if (isset($data['rewardable_type'])) {
                foreach ($data['rewardable_type'] as $key => $assetType) {
                    $reward = getAssetModelString(strtolower($data['rewardable_type'][$key]))::find($data['rewardable_id'][$key]);
                    if (!$reward) {
                        continue;
                    }
                    addAsset($assets, $reward, $data['quantity'][$key]);
                }
            }

            if (countAssets($assets) == 0) {
                throw new \Exception('You must select at least one reward to grant.');
            }

            foreach ($data['ids'] as $id) {
                if ($type == 'user') {
                    $user = User::find($id);
                    if (!$user) {
                        continue;
                    }

                    if (!$rewards = fillUserAssets($assets, $staff, $user, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']))) {
                        throw new \Exception('Failed to distribute rewards to user.');
                    }

                    if (!$this->logAdminAction($staff, 'Reward Grant', 'Granted Rewards ('.createRewardsString($rewards).') to '.$user->displayName)) {
                        throw new \Exception('Failed to log admin action.');
                    }

                    Notifications::create('USER_REWARD_GRANT', $user, [
                        'sender_url'    => $staff->url,
                        'sender_name'   => $staff->name,
                        'assets'        => createRewardsString($rewards),
                    ]);
                } else {
                    $character = Character::find($id);
                    if (!$character) {
                        continue;
                    }

                    if (!$rewards = fillCharacterAssets($assets, $staff, $character, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']))) {
                        throw new \Exception('Failed to distribute rewards to character.');
                    }

                    if (!$this->logAdminAction($staff, 'Reward Grant', 'Granted Rewards ('.createRewardsString($rewards).') to '.$character->displayName)) {
                        throw new \Exception('Failed to log admin action.');
                    }

                    Notifications::create('CHARACTER_REWARD_GRANT', $character->user, [
                        'character_name'   => $character->displayName,
                        'sender_url'       => $staff->url,
                        'sender_name'      => $staff->name,
                        'assets'           => createRewardsString($rewards),
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
