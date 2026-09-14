<?php

namespace App\Models\Criteria;

use App\Models\Model;

class DefaultCriteria extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'criteriondefault_id', 'criterion_id', 'min_requirements', 'criterion_currency_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'default_criteria';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'min_requirements' => 'array',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'criterion_id' => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'criterion_id' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS
    **********************************************************************************************/

    /**
     * Get the prompt attached to this criterion.
     */
    public function default() {
        return $this->belongsTo(CriterionDefault::class, 'criteriondefault_id');
    }

    /**
     * Get the criterion attached to this prompt.
     */
    public function criterion() {
        return $this->belongsTo(Criterion::class, 'criterion_id');
    }
}
