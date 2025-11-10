<?php

namespace App\Models\Shop;

use App\Models\Item\Item;
use App\Models\Model;

class ShopStockCost extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_stock_id', 'cost_type', 'cost_id', 'quantity', 'group',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_stock_costs';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the stock that this cost is associated with.
     */
    public function stock() {
        return $this->belongsTo(ShopStock::class, 'shop_stock_id');
    }

    /**
     * Get the item being stocked.
     */
    public function item() {
        $model = getAssetModelString(strtolower($this->cost_type));

        return $this->belongsTo($model, 'cost_id');
    }

    /**
     * Gets all of the other costs for this stock in the same group.
     */
    public function group() {
        return $this->hasMany(self::class, 'group');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Gets all of the other costs for this stock in the same group.
     */
    public function getItemsAttribute() {
        $model = getAssetModelString(strtolower($this->cost_type));

        return $model::all()->pluck('name', 'id');
    }
}
