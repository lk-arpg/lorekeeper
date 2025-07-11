<?php

namespace App\Models\Species;

use App\Models\Character\Sublist;
use App\Models\Feature\Feature;
use App\Models\Model;

class Species extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sort', 'has_image', 'description', 'parsed_description', 'masterlist_sub_id', 'is_visible', 'hash',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'specieses';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:specieses|between:3,100',
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
     * Get the subtypes for this species.
     */
    public function subtypes() {
        return $this->hasMany(Subtype::class);
    }

    /**
     * Get the sub masterlist for this species.
     */
    public function sublist() {
        return $this->belongsTo(Sublist::class, 'masterlist_sub_id');
    }

    /**
     * Get the features associated with this species.
     */
    public function features() {
        return $this->hasMany(Feature::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort species in default order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortStandard($query, $reverse = false) {
        return $query->orderBy('sort', $reverse ? 'ASC' : 'DESC')->orderBy('id');
    }

    /**
     * Scope a query to sort species in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort species by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query, $reverse = false) {
        return $query->orderBy('id', $reverse ? 'ASC' : 'DESC');
    }

    /**
     * Scope a query to show only visible species.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1);
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
        return '<a href="'.$this->url.'" class="display-species">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/species';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getSpeciesImageFileNameAttribute() {
        return $this->id.'-'.$this->hash.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getSpeciesImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getSpeciesImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->speciesImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/species?name='.$this->name);
    }

    /**
     * Gets the URL for a masterlist search of characters of this species.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        if ($this->masterlist_sub_id != 0 && $this->sublist->show_main == 0) {
            return url('sublist/'.$this->sublist->key.'?species_id='.$this->id);
        } else {
            return url('masterlist?species_id='.$this->id);
        }
    }

    /**
     * Gets the URL the visual index of this species' traits.
     *
     * @return string
     */
    public function getVisualTraitsUrlAttribute() {
        return url('/world/species/'.$this->id.'/traits');
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/species/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }
}
