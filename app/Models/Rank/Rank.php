<?php

namespace App\Models\Rank;

use App\Models\Model;
use Illuminate\Support\Arr;

class Rank extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'parsed_description', 'sort', 'color', 'icon',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ranks';
    /**
     * Validation rules for ranks.
     *
     * @var array
     */
    public static $rules = [
        'name'        => 'required|between:3,100',
        'description' => 'nullable',
        'color'       => 'nullable|regex:/^#?[0-9a-fA-F]{6}$/i',
        'icon'        => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the powers attached to this rank.
     */
    public function powers() {
        return $this->hasMany(RankPower::class);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Display the rank with its associated colour.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        if ($this->color) {
            return '<strong style="color: #'.$this->color.'">'.$this->name.'</strong>';
        }

        return $this->name;
    }

    /**
     * Check if the rank is the admin rank.
     *
     * @return bool
     */
    public function getIsAdminAttribute() {
        if ($this->id == self::orderBy('sort', 'DESC')->first()->id) {
            return true;
        }

        return false;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks if the current rank is high enough to edit a given rank.
     *
     * @param Rank $rank
     *
     * @return int
     */
    public function canEditRank($rank) {
        if (is_numeric($rank)) {
            $rank = self::find($rank);
        }
        if ($this->hasPower('edit_ranks')) {
            if ($this->isAdmin) {
                if ($rank->id != $this->id) {
                    return 1;
                } // can edit everything
                else {
                    return 2;
                } // limited edit: cannot edit sort order/powers
            } elseif ($this->sort > $rank->sort) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * Checks if the rank has a given power.
     *
     * @param RankPower $power
     *
     * @return bool
     */
    public function hasPower($power) {
        if ($this->isAdmin) {
            return true;
        }

        return $this->powers()->where('power', $power)->exists();
    }

    /**
     * Get the powers associated with the rank.
     *
     * @return array
     */
    public function getPowers() {
        if ($this->isAdmin) {
            return config('lorekeeper.powers');
        }
        $powers = $this->powers->pluck('power')->toArray();

        return Arr::only(config('lorekeeper.powers'), $powers);
    }
}
