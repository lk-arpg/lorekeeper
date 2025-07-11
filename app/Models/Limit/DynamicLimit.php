<?php

namespace App\Models\Limit;

use App\Models\Model;

class DynamicLimit extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'evaluation',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynamic_limits';

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * returns the displayName of the limit.
     */
    public function getDisplayNameAttribute() {
        return $this->name.' Check';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * preforms the evaluation of the dynamic limit in a try / except and returns true or false
     * if the evaluation is fails.
     */
    public function evaluate() {
        try {
            $eval = preg_replace('/<\?php/', '', $this->evaluation);
            $eval = preg_replace('/\n/', '', $eval);
            $eval = preg_replace('/\r/', '', $eval);

            return eval($eval);
        } catch (\Throwable $th) {
            return false;
        }
    }
}
