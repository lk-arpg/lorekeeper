<?php

namespace App\Models\Criteria;

use App\Models\Model;

class CriterionStep extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_active', 'summary', 'description', 'parsed_description', 'type', 'calc_type', 'input_calc_type', 'order', 'has_image', 'criterion_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'criterion_steps';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:character_categories|between:3,100',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:3,100',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the currency for this criterion.
     */
    public function criterion() {
        return $this->belongsTo('App\Models\Criteria\Criterion', 'criterion_id');
    }

    /**
     * Get all steps associated with the criterion.
     *
     * @param mixed|null $minRequirement
     */
    public function options($minRequirement = null) {
        $query = $this->hasMany('App\Models\Criteria\CriterionStepOption', 'criterion_step_id')->orderBy('order');
        if ($minRequirement) {
            $collection = $query->get();
            $sliceAt = $collection->where('id', $minRequirement)->keys()->first();
            $query = $collection->slice($sliceAt);
        }

        return $query;
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/criteria';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->ImageFileName);
    }

    /**********************************************************************************************

       SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }
}
