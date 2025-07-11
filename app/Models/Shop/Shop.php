<?php

namespace App\Models\Shop;

use App\Models\Item\Item;
use App\Models\Model;
use Carbon\Carbon;

class Shop extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sort', 'has_image', 'description', 'parsed_description', 'is_active', 'hash', 'is_staff', 'use_coupons', 'is_fto', 'allowed_coupons', 'is_timed_shop', 'start_at', 'end_at',
        'is_hidden', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shops';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'            => 'array',
        'allowed_coupons' => 'array',
        'end_at'          => 'datetime',
        'start_at'        => 'datetime',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:shops|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the shop stock.
     */
    public function stock() {
        return $this->hasMany(ShopStock::class);
    }

    /**
     * Get the shop stock as items for display purposes.
     *
     * @param mixed|null $model
     * @param mixed|null $type
     */
    public function displayStock($model = null, $type = null) {
        if (!$model || !$type) {
            return $this->belongsToMany(Item::class, 'shop_stock')->where('stock_type', 'Item')->withPivot('item_id', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'purchase_limit', 'id', 'is_timed_stock')
                ->wherePivot('is_visible', 1)->where(function ($query) {
                    $query->whereNull('shop_stock.start_at')
                        ->orWhere('shop_stock.start_at', '<', Carbon::now());
                })->where(function ($query) {
                    $query->whereNull('shop_stock.end_at')
                        ->orWhere('shop_stock.end_at', '>', Carbon::now());
                });
        }

        return $this->belongsToMany($model, 'shop_stock', 'shop_id', 'item_id')->where('stock_type', $type)->withPivot('item_id', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'purchase_limit', 'id', 'is_timed_stock')
            ->wherePivot('is_visible', 1)->where(function ($query) {
                $query->whereNull('shop_stock.start_at')
                    ->orWhere('shop_stock.start_at', '<', Carbon::now());
            })->where(function ($query) {
                $query->whereNull('shop_stock.end_at')
                    ->orWhere('shop_stock.end_at', '>', Carbon::now());
            });
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the shop's name, linked to its purchase page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-shop">'.(!$this->isActive ? '<i class="fas fa-eye-slash"></i> ' : '').$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/shops';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getShopImageFileNameAttribute() {
        return $this->id.'-'.$this->hash.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getShopImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getShopImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->shopImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('shops/'.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/shops/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**
     * Returns the days the shop is available, if set.
     */
    public function getDaysAttribute() {
        return $this->data['shop_days'] ?? null;
    }

    /**
     * Returns the months the shop is available, if set.
     */
    public function getMonthsAttribute() {
        return $this->data['shop_months'] ?? null;
    }

    /**
     * Returns if this shop should be active or not.
     * We dont account for is_visible here, as this is used for checking both visible and invisible shop.
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

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Gets all the coupons useable in the shop.
     */
    public function getAllAllowedCouponsAttribute() {
        if (!$this->use_coupons || !$this->allowed_coupons) {
            return;
        }
        // Get the coupons from the id in allowed_coupons
        $coupons = Item::whereIn('id', $this->allowed_coupons)->get();

        return $coupons;
    }

    /**
     * Gets the shop's stock costs.
     *
     * @param mixed $id
     */
    public function displayStockCosts($id) {
        return $this->stock()->where('id', $id)->first()->displayCosts();
    }
}
