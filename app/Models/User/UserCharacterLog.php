<?php

namespace App\Models\User;

use App\Models\Character\Character;
use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserCharacterLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'sender_id', 'sender_alias', 'recipient_id', 'recipient_alias',
        'log', 'log_type', 'data', 'sender_url', 'recipient_url',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_character_log';
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
     * Displays the sender's alias, linked to their profile.
     *
     * @return string
     */
    public function getDisplaySenderAliasAttribute() {
        return prettyProfileLink($this->sender_url);
    }

    /**
     * Displays the recipient's alias, linked to their profile.
     *
     * @return string
     */
    public function getDisplayRecipientAliasAttribute() {
        return prettyProfileLink($this->recipient_url);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void {
        static::addGlobalScope('visible', function (Builder $builder) {
            if (!(Auth::check()) || !(Auth::user()->hasPower('manage_characters'))) {
                $builder->whereRelation('character', 'is_visible', 1);
            }
        });
    }
}
