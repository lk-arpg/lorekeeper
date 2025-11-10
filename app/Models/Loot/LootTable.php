<?php

namespace App\Models\Loot;

use App\Models\Item\Item;
use App\Models\Model;
use App\Models\Rarity;

class LootTable extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loot_tables';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'         => 'required',
        'display_name' => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'         => 'required',
        'display_name' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the loot data for this loot table.
     */
    public function loot() {
        return $this->hasMany(Loot::class, 'loot_table_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<span class="display-loot">'.$this->attributes['display_name'].'</span> '.add_help('This reward is random.');
    }

    /**
     * Gets the loot table's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'loot_tables';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/loot-tables/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Rolls on the loot table and consolidates the rewards.
     *
     * @param int $quantity
     *
     * @return \Illuminate\Support\Collection
     */
    public function roll($quantity = 1) {
        $rewards = createAssetsArray();

        $loot = $this->loot;
        $totalWeight = 0;
        foreach ($loot as $l) {
            $totalWeight += $l->weight;
        }

        for ($i = 0; $i < $quantity; $i++) {
            $roll = mt_rand(0, $totalWeight - 1);
            $result = null;
            $prev = null;
            $count = 0;
            foreach ($loot as $l) {
                $count += $l->weight;

                if ($roll < $count) {
                    $result = $l;
                    break;
                }
                $prev = $l;
            }
            if (!$result) {
                $result = $prev;
            }

            if ($result) {
                // If this is chained to another loot table, roll on that table
                if ($result->rewardable_type == 'LootTable') {
                    $rewards = mergeAssetsArrays($rewards, $result->reward->roll($result->quantity));
                } elseif ($result->rewardable_type == 'ItemCategory' || $result->rewardable_type == 'ItemCategoryRarity') {
                    $rewards = mergeAssetsArrays($rewards, $this->rollCategory($result->rewardable_id, $result->quantity, ($result->data['criteria'] ?? null), ($result->data['rarity'] ?? null)));
                } elseif ($result->rewardable_type == 'ItemRarity') {
                    $rewards = mergeAssetsArrays($rewards, $this->rollRarityItem($result->quantity, $result->data['criteria'], $result->data['rarity']));
                } else {
                    addAsset($rewards, $result->reward, $result->quantity);
                }
            }
        }

        return $rewards;
    }

    /**
     * Rolls on an item category.
     *
     * @param int        $id
     * @param int        $quantity
     * @param string     $rarity
     * @param mixed|null $criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rollCategory($id, $quantity = 1, $criteria = null, $rarity = null) {
        $rewards = createAssetsArray();

        if (isset($criteria) && $criteria && isset($rarity) && $rarity) {
            $rarity = Rarity::find($rarity);
            if (!$rarity) {
                throw new \Exception('Invalid rarity!');
            }

            $loot = Item::where('item_category_id', $id)->released()->whereNotNull('data')->get()->filter(function ($item) use ($criteria, $rarity) {
                $itemRarity = Rarity::find($item->data['rarity_id'] ?? null);
                if (!$itemRarity) {
                    return false;
                }

                // check the sort order of the rarity
                return eval('return '.$itemRarity->sort.$criteria.$rarity->sort.';');
            })->values();
        } else {
            $loot = Item::where('item_category_id', $id)->released()->get();
        }
        if (!$loot->count()) {
            throw new \Exception('There are no items to select from!');
        }

        $totalWeight = $loot->count();

        for ($i = 0; $i < $quantity; $i++) {
            $roll = mt_rand(0, $totalWeight - 1);
            $result = $loot[$roll];

            if ($result) {
                // If this is chained to another loot table, roll on that table
                addAsset($rewards, $result, 1);
            }
        }

        return $rewards;
    }

    /**
     * Rolls on an item rarity.
     *
     * @param int    $quantity
     * @param string $rarity
     * @param mixed  $criteria
     *
     * @return \Illuminate\Support\Collection
     */
    public function rollRarityItem($quantity, $criteria, $rarity) {
        $rewards = createAssetsArray();

        $rarity = Rarity::find($rarity);
        if (!$rarity) {
            throw new \Exception('Invalid rarity!');
        }

        $loot = Item::released()->whereNotNull('data')->get()->filter(function ($item) use ($criteria, $rarity) {
            $itemRarity = Rarity::find($item->data['rarity_id'] ?? null);
            if (!$itemRarity) {
                return false;
            }

            // check the sort order of the rarity
            return eval('return '.$itemRarity->sort.$criteria.$rarity->sort.';');
        })->values();
        if (!$loot->count()) {
            throw new \Exception('There are no items to select from!');
        }

        $totalWeight = $loot->count();

        for ($i = 0; $i < $quantity; $i++) {
            $roll = mt_rand(0, $totalWeight - 1);
            $result = $loot[$roll];

            if ($result) {
                // If this is chained to another loot table, roll on that table
                addAsset($rewards, $result, 1);
            }
        }

        return $rewards;
    }
}
