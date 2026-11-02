<?php

namespace App\Models\Character;

use App\Models\Model;
use App\Models\User\User;

class CharacterLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'sender_id', 'sender_alias', 'recipient_id', 'recipient_alias',
        'log', 'log_type', 'data', 'change_log', 'sender_url', 'recipient_url',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_log';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'       => 'array',
        'change_log' => 'array',
    ];

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
     * Get the user who initiated the logged action.
     */
    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the logged action.
     */
    public function recipient() {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the character that is the target of the action.
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the recipient's alias if applicable.
     *
     * @return string
     */
    public function getDisplayRecipientAliasAttribute() {
        if ($this->recipient_url) {
            return prettyProfileLink($this->recipient_url);
        } else {
            return '---';
        }
    }
}
