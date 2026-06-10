<?php

namespace App\Models\Prompt;

use App\Models\Criteria\Criterion;
use App\Models\Model;

class PromptCriterion extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prompt_id', 'criterion_id', 'min_requirements', 'criterion_currency_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prompt_criteria';

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
    public function prompt() {
        return $this->belongsTo(Prompt::class, 'prompt_id');
    }

    /**
     * Get the criterion attached to this prompt.
     */
    public function criterion() {
        return $this->belongsTo(Criterion::class, 'criterion_id');
    }

    /**
     * Returns true if this is a character criteria.
     */
    public function getCharacterCriteriaAttribute() {
        if ($this->criterion->currency->is_character_owned == 1) {
            return true;
        } else {
            return false;
        }
    }
}
