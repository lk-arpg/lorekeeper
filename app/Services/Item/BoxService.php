<?php

namespace App\Services\Item;

use App\Models\Item\Item;
use App\Services\InventoryManager;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class BoxService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Box Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of box type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param object $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        $rewards = [];
        if ($tag->data) {
            $assets = parseAssetData($tag->data);
            foreach ($assets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $rewards[] = (object) [
                        'rewardable_recipient' => 'User',
                        'rewardable_type'      => $class,
                        'rewardable_id'        => $id,
                        'quantity'             => $asset['quantity'],
                    ];
                }
            }
        }

        return $rewards;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param object $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        DB::beginTransaction();

        try {
            // If there's no data, return.
            if (!isset($data['rewardable_type'])) {
                return true;
            }

            // The data will be stored as an asset table.
            // First build the asset table, then prepare it for storage.
            $assets = createAssetsArray();
            foreach ($data['rewardable_type'] as $key => $r) {
                switch ($r) {
                    case 'Item':
                        $type = 'App\Models\Item\Item';
                        break;
                    case 'Currency':
                        $type = 'App\Models\Currency\Currency';
                        break;
                    case 'LootTable':
                        $type = 'App\Models\Loot\LootTable';
                        break;
                    case 'Raffle':
                        $type = 'App\Models\Raffle\Raffle';
                        break;
                }
                $asset = $type::find($data['rewardable_id'][$key]);
                addAsset($assets, $asset, $data['quantity'][$key]);
            }
            $assets = getDataReadyAssets($assets);

            $tag->update(['data' => $assets]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param \App\Models\User\UserItem $stacks
     * @param \App\Models\User\User     $user
     * @param array                     $data
     *
     * @return bool
     */
    public function act($stacks, $user, $data) {
        DB::beginTransaction();

        try {
            foreach ($stacks as $key=> $stack) {
                // We don't want to let anyone who isn't the owner of the box open it,
                // so do some validation...
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }

                // Next, try to delete the box item. If successful, we can start distributing rewards.
                if ((new InventoryManager)->debitStack($stack->user, 'Box Opened', ['data' => ''], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        // Distribute user rewards
                        if (!$rewards = fillUserAssets(parseAssetData($stack->item->tag('box')->data), $user, $user, 'Box Rewards', [
                            'data' => 'Received rewards from opening '.$stack->item->name,
                        ])) {
                            throw new \Exception('Failed to open box.');
                        }
                        flash($this->getBoxRewardsString($rewards));
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param array $rewards
     *
     * @return string
     */
    private function getBoxRewardsString($rewards) {
        return 'You have received: '.createRewardsString($rewards);
    }
}
