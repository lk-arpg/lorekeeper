<?php

namespace App\Models\Loot;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Model;

class Loot extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loot_table_id', 'rewardable_type', 'rewardable_id',
        'quantity', 'weight', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loots';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'rewardable_type' => 'required',
        'rewardable_id'   => 'required',
        'quantity'        => 'required|integer|min:1',
        'weight'          => 'required|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'rewardable_type' => 'required',
        'rewardable_id'   => 'required',
        'quantity'        => 'required|integer|min:1',
        'weight'          => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the reward attached to the loot entry.
     */
    public function reward() {
        switch ($this->rewardable_type) {
            case 'Item':
                return $this->belongsTo(Item::class, 'rewardable_id');
            case 'ItemRarity':
                return $this->belongsTo(Item::class, 'rewardable_id');
            case 'Currency':
                return $this->belongsTo(Currency::class, 'rewardable_id');
            case 'LootTable':
                return $this->belongsTo(LootTable::class, 'rewardable_id');
            case 'ItemCategory':
                return $this->belongsTo(ItemCategory::class, 'rewardable_id');
            case 'ItemCategoryRarity':
                return $this->belongsTo(ItemCategory::class, 'rewardable_id');
            case 'None':
                // Laravel requires a relationship instance to be returned (cannot return null), so returning one that doesn't exist here.
                return $this->belongsTo(self::class, 'rewardable_id', 'loot_table_id')->whereNull('loot_table_id');
        }

        return null;
    }
}
