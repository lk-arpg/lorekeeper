<?php

namespace App\Models\Criteria;

use App\Models\Model;

class CriterionDefault extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'summary',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'criteria_defaults';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:criteria_defaults|between:3,100',
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

        ACCESSORS

     **********************************************************************************************/
    /**
     * Get the criteria attached to this prompt.
     */
    public function criteria() {
        return $this->hasMany('App\Models\Criteria\DefaultCriteria', 'criteriondefault_id');
    }
}
