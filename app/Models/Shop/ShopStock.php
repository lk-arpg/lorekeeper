<?php

namespace App\Models\Shop;

use App\Models\Item\Item;
use App\Models\Model;
use Carbon\Carbon;

class ShopStock extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id', 'item_id', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'sort', 'purchase_limit', 'purchase_limit_timeframe', 'is_fto', 'stock_type', 'is_visible',
        'restock', 'restock_quantity', 'restock_interval', 'range', 'disallow_transfer', 'is_timed_stock', 'start_at', 'end_at', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_stock';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'     => 'array',
        'end_at'   => 'datetime',
        'start_at' => 'datetime',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'purchase_limit_timeframe' => 'in:lifetime,yearly,monthly,weekly,daily',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the item being stocked.
     */
    public function item() {
        $model = getAssetModelString(strtolower($this->stock_type));

        if (!class_exists($model)) {
            // Laravel requires a relationship instance to be returned (cannot return null), so returning one that doesn't exist here.
            return $this->belongsTo(self::class, 'id', 'item_id')->whereNull('item_id');
        }

        return $this->belongsTo($model);
    }

    /**
     * Get the shop that holds this item.
     */
    public function shop() {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the costs associated with this stock.
     */
    public function costs() {
        return $this->hasMany(ShopStockCost::class, 'shop_stock_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scopes active stock.
     *
     * @param mixed $query
     */
    public function scopeActive($query) {
        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns if this stock should be active or not.
     * We dont account for is_visible here, as this is used for checking both visible and invisible stock.
     */
    public function getIsActiveAttribute() {
        if ($this->start_at && $this->start_at > Carbon::now()) {
            return false;
        }

        if ($this->end_at && $this->end_at < Carbon::now()) {
            return false;
        }

        if ($this->days && !in_array(Carbon::now()->format('l'), $this->days)) {
            return false;
        }

        if ($this->months && !in_array(Carbon::now()->format('F'), $this->months)) {
            return false;
        }

        return true;
    }

    /**
     * Returns if the stock is random or not.
     */
    public function getIsRandomAttribute() {
        return isset($this->data['is_random']) && $this->data['is_random'];
    }

    /**
     * Returns if the stock is category based or not.
     */
    public function getIsCategoryAttribute() {
        $model = getAssetModelString(strtolower($this->stock_type));

        return isset($this->data['is_category']) && $this->data['is_category'] && class_exists($model.'Category');
    }

    /**
     * Returns the stock category id, only if the stock is category based.
     */
    public function getCategoryIdAttribute() {
        return $this->isCategory ? ($this->data['category_id'] ?? null) : null;
    }

    /**
     * Returns the days the stock is available, if set.
     */
    public function getDaysAttribute() {
        return $this->data['stock_days'] ?? null;
    }

    /**
     * Returns the months the stock is available, if set.
     */
    public function getMonthsAttribute() {
        return $this->data['stock_months'] ?? null;
    }

    /**
     * Returns all of the existing groups based on the costs.
     */
    public function getGroupsAttribute() {
        return $this->costs()->get()->pluck('group')->unique();
    }

    /**
     * Makes the costs an array of arrays.
     */
    public function getCostGroupsAttribute() {
        $costs = $this->costs()->get()->groupBy('group');
        $groupedCosts = [];
        foreach ($costs as $group => $costGroup) {
            $assets = createAssetsArray();
            foreach ($costGroup as $cost) {
                addAsset($assets, $cost->item, $cost->quantity);
            }
            $groupedCosts[$group] = $assets;
        }

        return $groupedCosts;
    }

    /*
     * Gets the current date associated to the current stocks purchase limit timeframe
     */
    public function getPurchaseLimitDateAttribute() {
        switch ($this->purchase_limit_timeframe) {
            case 'yearly':
                $date = strtotime('January 1st');
                break;
            case 'monthly':
                $date = Carbon::now()->startOfMonth()->timestamp;
                break;
            case 'weekly':
                $date = strtotime('last sunday');
                break;
            case 'daily':
                $date = strtotime('midnight');
                break;
            default:
                $date = null;
        }

        return $date;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Returns formatted lists of costs for display.
     */
    public function displayCosts() {
        $display = [];
        $costs = $this->costGroups;
        foreach ($costs as $group => $groupCosts) {
            $display[] = createRewardsString($groupCosts);
        }

        return count($display) ? implode(' <i>OR</i> ', $display) : null;
    }

    /**
     * Returns the costs in the format group => rewardString for a form select.
     */
    public function costForm() {
        $costs = $this->costGroups;
        $select = [];
        foreach ($costs as $group => $groupCosts) {
            $select[$group] = createRewardsString($groupCosts, false);
        }

        return $select;
    }

    /**
     * Returns if a group can use coupons.
     *
     * @param mixed $group
     */
    public function canGroupUseCoupons($group) {
        return in_array($group, $this->data['can_group_use_coupon'] ?? []);
    }

    /**
     * Displays when this stock is available in human readable format.
     */
    public function displayTime() {
        // {!! '<div>' . ($stock->start_at ? pretty_date($stock->start_at) : 'Now') . ' - ' . ($stock->end_at ?  pretty_date($stock->end_at) : 'Always') . '</div>' !!}
        $days = $this->days;
        $months = $this->months;
        $string = '<div>';
        // if start_at and end_at are set, we need to show that its avaialble only during that time and THEN only on the days and months
        if ($this->start_at && $this->end_at) {
            $string .= pretty_date($this->start_at).' - '.pretty_date($this->end_at);
        } elseif ($this->start_at) {
            $string .= 'From '.pretty_date($this->start_at);
        } elseif ($this->end_at) {
            $string .= 'Until '.pretty_date($this->end_at);
        }

        if ($days) {
            $string .= '<br>Available on '.implode(', ', $days);
        }

        if ($months) {
            $string .= '<br>During '.implode(', ', $months);
        }

        $string .= '</div>';

        return $string;
    }
}
