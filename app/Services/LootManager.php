<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\Loot\LootTable;
use App\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LootManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Loot Manager
    |--------------------------------------------------------------------------
    |
    | Handles the granting of loot tables to users.
    |
    */

    /**
     * Grants a loot table to multiple users.
     *
     * @param array $data
     * @param User  $staff
     *
     * @return bool
     */
    public function grantLootTables($data, $staff) {
        DB::beginTransaction();

        try {
            foreach ($data['quantities'] as $q) {
                if ($q <= 0) {
                    throw new \Exception('All quantities must be at least 1.');
                }
            }

            // Process names
            $users = User::find($data['names']);
            if (count($users) != count($data['names'])) {
                throw new \Exception('An invalid user was selected.');
            }

            $keyed_quantities = [];
            array_walk($data['loot_table_ids'], function ($id, $key) use (&$keyed_quantities, $data) {
                if ($id != null && !in_array($id, array_keys($keyed_quantities), true)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process table
            $tables = LootTable::find($data['loot_table_ids']);
            if (!count($tables)) {
                throw new \Exception('No valid loot tables found.');
            }

            foreach ($users as $user) {
                foreach ($tables as $table) {
                    if (!$this->logAdminAction($staff, 'Loot Table Grant', 'Granted '.$keyed_quantities[$table->id].' '.$table->displayName.' to '.$user->displayname)) {
                        throw new \Exception('Failed to log admin action.');
                    }
                    if ($assets = $this->grantLootTable($staff, $user, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']), $table, $keyed_quantities[$table->id])) {
                        Notifications::create('LOOT_TABLE_GRANT', $user, [
                            'item_name'     => $table->name,
                            'item_quantity' => $keyed_quantities[$table->id],
                            'sender_url'    => $staff->url,
                            'sender_name'   => $staff->name,
                            'assets'        => createRewardsString($assets),
                        ]);
                    } else {
                        throw new \Exception('Failed to credit loot table to '.$user->name.'.');
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /*
     * Grants loot tables to a user.
     *
     * @param User      $sender
     * @param User      $recipient
     * @param string    $type
     * @param array     $data
     * @param LootTable $table
     * @param int       $quantity
     */
    public function grantLootTable($sender, $recipient, $type, $data, $table, $quantity) {
        DB::beginTransaction();

        try {
            $assets = createAssetsArray(false);
            addAsset($assets, $table, $quantity);

            if (!$assets = fillUserAssets($assets, $sender, $recipient, $type, $data)) {
                throw new \Exception('Failed to distribute rewards to user.');
            }

            return $this->commitReturn($assets);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
