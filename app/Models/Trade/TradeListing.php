<?php

namespace App\Models\Trade;

use App\Models\Character\Character;
use App\Models\Model;
use App\Models\User\User;
use App\Traits\Commentable;
use Carbon\Carbon;

class TradeListing extends Model {
    use Commentable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'comments', 'contact', 'expires_at', 'title',  'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trade_listings';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'       => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Validation rules for character creation.
     *
     * @var array
     */
    public static $createRules = [
        'title'        => 'nullable|between:3,50',
        'comments'     => 'nullable',
        'contact'      => 'required',
        'seeking_etc'  => 'nullable|between:3,100',
        'offering_etc' => 'nullable|between:3,100',
    ];

    /**
     * Validation rules for character updating.
     *
     * @var array
     */
    public static $updateRules = [
        'title'        => 'nullable|between:3,50',
        'comments'     => 'nullable',
        'contact'      => 'required',
        'seeking_etc'  => 'nullable|between:3,100',
        'offering_etc' => 'nullable|between:3,100',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who posted the trade listing.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active trade listings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where(function ($query) {
            $query->where('expires_at', '>', Carbon::now())->orWhere(function ($query) {
                $query->where('expires_at', '>=', Carbon::now());
            });
        });
    }

    /**
     * Scope a query to only include active trade listings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query) {
        return $query->where(function ($query) {
            $query->where('expires_at', '<', Carbon::now())->orWhere(function ($query) {
                $query->where('expires_at', '<=', Carbon::now());
            });
        });
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the Display Name of the trade listing.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        if ($this->title == null) {
            return $this->user->displayName.'\'s <a href="'.$this->url.'">Trade Listing</a> (#'.$this->id.')';
        } else {
            return '<a href="'.$this->url.'" data-toggle="tooltip" title="'.$this->user->name.'\'s trade listing.">'.$this->title.'</a> (Trade Listing #'.$this->id.')';
        }
    }

    /**
     * Gets the Display Name of the trade listing with the title portion somewhat shorter.
     *
     * @return string
     */
    public function getDisplayNameShortAttribute() {
        if ($this->title == null) {
            return $this->user->displayName.'\'s <a href="'.$this->url.'">Trade Listing</a> (#'.$this->id.')';
        } else {
            return '<a href="'.$this->url.'" data-toggle="tooltip" title="'.$this->user->name.'\'s trade listing.">'.$this->title.'</a>';
        }
    }

    /**
     * Gets the name for the trade listing for use in forms.
     *
     * @return string
     */
    public function getFormNameAttribute() {
        return $this->title.' (Trade Listing #'.$this->id.')';
    }

    /**
     * Check if the trade listing is active.
     *
     * @return bool
     */
    public function getIsActiveAttribute() {
        if ($this->expires_at >= Carbon::now()) {
            return true;
        }

        return false;
    }

    /**
     * Gets the URL of the trade listing.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('trades/listings/'.$this->id);
    }

    /**
     * Returns the seeking data for use in loot rows.
     */
    public function getSeekingDataAttribute() {
        if (!isset($this->data['seeking'])) {
            return [];
        }

        $assets = parseAssetData($this->data['seeking']);
        $rewards = [];
        foreach ($assets as $type => $a) {
            $class = getAssetModelString($type, false);
            foreach ($a as $id => $asset) {
                $rewards[] = (object) [
                    'rewardable_type' => $class,
                    'rewardable_id'   => $id,
                    'quantity'        => $asset['quantity'],
                ];
            }
        }

        return $rewards;
    }

    /**
     * Gets the selected inventory for the trade listing.
     *
     * @return array
     */
    public function getInventoryAttribute() {
        return $this->data && isset($this->data['offering']['user_items']) ? $this->data['offering']['user_items'] : [];
    }

    /**
     * Gets the currencies of the given user for selection.
     *
     * @return array
     */
    public function getCurrenciesAttribute() {
        return $this->data && isset($this->data['offering']['currencies']) ? array_keys($this->data['offering']['currencies']) : [];
    }

    /**
     * Gets the characters from the offering (you cannot seek characters directly).
     *
     * @return array
     */
    public function getCharactersAttribute() {
        return $this->data && isset($this->data['offering']) && isset($this->data['offering']['characters']) ? array_keys($this->data['offering']['characters']) : [];
    }
}
