<?php

namespace App\Models\Species;

use App\Models\Feature\Feature;
use App\Models\Model;

class Subtype extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'species_id', 'name', 'sort', 'has_image', 'description', 'parsed_description', 'is_visible', 'hash',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subtypes';

    /**
     * Accessors to append to the model.
     *
     * @var array
     */
    protected $appends = [
        'name_with_species',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'species_id'  => 'required',
        'name'        => 'required|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'species_id'  => 'required',
        'name'        => 'required|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the species the subtype belongs to.
     */
    public function species() {
        return $this->belongsTo(Species::class, 'species_id');
    }

    /**
     * Get the features associated with this subtype.
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
     * Scope a query to sort subtypes in alphabetical order.
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
     * Scope a query to sort subtypes in species order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortSpecies($query) {
        $ids = Species::orderBy('sort', 'DESC')->pluck('id')->toArray();

        return count($ids) ? $query->orderBy(DB::raw('FIELD(species_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort subtypes by newest first.
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
     * Scope a query to show only visible subtypes.
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
     * Displays the subtype's name and species.
     *
     * @return string
     */
    public function getNameWithSpeciesAttribute() {
        return $this->name.' ['.$this->species->name.' Subtype]';
    }

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-subtype">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/subtypes';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getSubtypeImageFileNameAttribute() {
        return $this->id.'-'.$this->hash.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getSubtypeImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getSubtypeImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->subtypeImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/subtypes?name='.$this->name);
    }

    /**
     * Gets the URL for a masterlist search of characters of this species subtype.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        return url('masterlist?subtype_ids[]='.$this->id);
    }

    /**
     * Gets the URL the visual index of this subtype's traits.
     *
     * @return string
     */
    public function getVisualTraitsUrlAttribute() {
        return url('/world/subtypes/'.$this->id.'/traits');
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/subtypes/edit/'.$this->id);
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
