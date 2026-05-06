<?php

namespace App\Services;

use App\Models\Loot\Loot;
use App\Models\Loot\LootTable;
use App\Models\Prompt\PromptReward;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LootService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Loot Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of loot tables.
    |
    */

    /**
     * Creates a loot table.
     *
     * @param array $data
     *
     * @return bool|LootTable
     */
    public function createLootTable($data) {
        DB::beginTransaction();

        try {
            // More specific validation
            foreach ($data['rewardable_type'] as $key => $type) {
                if (!$type) {
                    throw new \Exception('Loot type is required.');
                }
                if ($type != 'ItemRarity' && !$data['rewardable_id'][$key]) {
                    throw new \Exception('Reward is required.');
                }
                if (!$data['quantity'][$key] || $data['quantity'][$key] < 1) {
                    throw new \Exception('Quantity is required and must be an integer greater than 0.');
                }
                if (!$data['weight'][$key] || $data['weight'][$key] < 1) {
                    throw new \Exception('Weight is required and must be an integer greater than 0.');
                }
                if ($type == 'ItemCategoryRarity') {
                    if (!isset($data['criteria'][$key]) || !$data['criteria'][$key]) {
                        throw new \Exception('Criteria is required for conditional item categories.');
                    }
                    if (!isset($data['rarity'][$key]) || !$data['rarity'][$key]) {
                        throw new \Exception('A rarity is required for conditional item categories.');
                    }
                }
            }

            $table = LootTable::create(Arr::only($data, ['name', 'display_name']));

            $this->populateLootTable($table, Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity', 'weight', 'criteria', 'rarity']));

            return $this->commitReturn($table);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a loot table.
     *
     * @param LootTable $table
     * @param array     $data
     *
     * @return bool|LootTable
     */
    public function updateLootTable($table, $data) {
        DB::beginTransaction();

        try {
            // More specific validation
            foreach ($data['rewardable_type'] as $key => $type) {
                if (!$type) {
                    throw new \Exception('Loot type is required.');
                }
                if ($type != 'ItemRarity' && !$data['rewardable_id'][$key]) {
                    throw new \Exception('Reward is required.');
                }
                if (!$data['quantity'][$key] || $data['quantity'][$key] < 1) {
                    throw new \Exception('Quantity is required and must be an integer greater than 0.');
                }
                if (!$data['weight'][$key] || $data['weight'][$key] < 1) {
                    throw new \Exception('Weight is required and must be an integer greater than 0.');
                }
                if ($type == 'ItemCategoryRarity') {
                    if (!isset($data['criteria'][$key]) || !$data['criteria'][$key]) {
                        throw new \Exception('Criteria is required for conditional item categories.');
                    }
                    if (!isset($data['rarity'][$key]) || !$data['rarity'][$key]) {
                        throw new \Exception('A rarity is required for conditional item categories.');
                    }
                }
            }

            $table->update(Arr::only($data, ['name', 'display_name']));

            $this->populateLootTable($table, Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity', 'weight', 'criteria', 'rarity']));

            return $this->commitReturn($table);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a loot table.
     *
     * @param LootTable $table
     *
     * @return bool
     */
    public function deleteLootTable($table) {
        DB::beginTransaction();

        try {
            // Check first if the table is currently in use
            // - Prompts
            // - Box rewards (unfortunately this can't be checked easily)
            if (PromptReward::where('rewardable_type', 'LootTable')->where('rewardable_id', $table->id)->exists()) {
                throw new \Exception('A prompt uses this table to distribute rewards. Please remove it from the rewards list first.');
            }

            $table->loot()->delete();
            $table->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles the creation of loot for a loot table.
     *
     * @param LootTable $table
     * @param array     $data
     */
    private function populateLootTable($table, $data) {
        // Clear the old loot...
        $table->loot()->delete();

        foreach ($data['rewardable_type'] as $key => $type) {
            if ($type == 'ItemCategoryRarity' || $type == 'ItemRarity') {
                $lootData = [
                    'criteria' => $data['criteria'][$key],
                    'rarity'   => $data['rarity'][$key],
                ];
            }

            Loot::create([
                'loot_table_id'   => $table->id,
                'rewardable_type' => $type,
                'rewardable_id'   => $data['rewardable_id'][$key] ?? 1,
                'quantity'        => $data['quantity'][$key],
                'weight'          => $data['weight'][$key],
                'data'            => isset($lootData) ? json_encode($lootData) : null,
            ]);
        }
    }
}
