<?php

namespace App\Models;

use App\Models\User\User;

class Invitation extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'user_id', 'recipient_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invitations';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who generated the invitation code.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created their account using the invitation code.
     */
    public function recipient() {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
