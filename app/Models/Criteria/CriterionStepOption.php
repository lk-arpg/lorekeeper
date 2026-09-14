<?php

namespace App\Models\Criteria;

use App\Models\Model;

class CriterionStepOption extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_active', 'criterion_step_id', 'summary', 'description', 'parsed_description', 'amount', 'order',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'criterion_step_options';

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
    public function step() {
        return $this->belongsTo('App\Models\Criteria\CriterionStep', 'criterion_step_id');
    }

    /**********************************************************************************************

       SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible criterions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }
}
