<?php

namespace App\Models;

use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\User\User;

class Trade extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'recipient_id', 'comments',
        'status', 'is_sender_confirmed', 'is_recipient_confirmed', 'is_sender_trade_confirmed', 'is_recipient_trade_confirmed',
        'is_approved', 'reason', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trades';
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
     * Get the user who initiated the trade.
     */
    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the trade.
     */
    public function recipient() {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the staff member who approved the character transfer.
     */
    public function staff() {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    public function scopeCompleted($query) {
        return $query->where('status', 'Completed')->orWhere('status', 'Rejected');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Check if the trade is active.
     *
     * @return bool
     */
    public function getIsActiveAttribute() {
        if ($this->status == 'Pending') {
            return true;
        }

        if (Settings::get('open_transfers_queue')) {
            if ($this->status == 'Accepted' && $this->is_approved == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the trade can be confirmed.
     *
     * @return bool
     */
    public function getIsConfirmableAttribute() {
        if ($this->is_sender_confirmed && $this->is_recipient_confirmed) {
            return true;
        }

        return false;
    }

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Gets the URL of the trade.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('trades/'.$this->id);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Gets all characters involved in the trade.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCharacterData() {
        return Character::with('user')->whereIn('id', array_merge($this->getCharacters($this->sender), $this->getCharacters($this->recipient)))->get();
    }

    /**
     * Gets the inventory of the given user for selection.
     *
     * @param User $user
     *
     * @return array
     */
    public function getInventory($user) {
        $type = $this->sender_id == $user->id ? 'sender' : 'recipient';
        $inventory = $this->data && isset($this->data[$type]) && isset($this->data[$type]['user_items']) ? $this->data[$type]['user_items'] : [];

        return $inventory;
    }

    /**
     * Gets the characters of the given user for selection.
     *
     * @param User $user
     *
     * @return array
     */
    public function getCharacters($user) {
        $type = $this->sender_id == $user->id ? 'sender' : 'recipient';
        $characters = $this->data && isset($this->data[$type]) && isset($this->data[$type]['characters']) ? $this->data[$type]['characters'] : [];
        if ($characters) {
            $characters = array_keys($characters);
        }

        return $characters;
    }

    /**
     * Gets the currencies of the given user for selection.
     *
     * @param User $user
     *
     * @return array
     */
    public function getCurrencies($user) {
        $type = $this->sender_id == $user->id ? 'sender' : 'recipient';

        return $this->data && isset($this->data[$type]) && isset($this->data[$type]['currencies']) ? $this->data[$type]['currencies'] : [];
    }
}
