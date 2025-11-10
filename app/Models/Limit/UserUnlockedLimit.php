<?php

namespace App\Models\Limit;

use App\Models\Model;
use App\Models\User\User;

class UserUnlockedLimit extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'object_model', 'object_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_unlocked_limits';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this set of settings belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
