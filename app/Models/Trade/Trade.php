<?php

namespace App\Models\Trade;

use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Model;
use App\Models\User\User;
use App\Models\User\UserItem;

class Trade extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'recipient_id', 'comments',
        'status', 'is_sender_confirmed', 'is_recipient_confirmed', 'is_sender_trade_confirmed', 'is_recipient_trade_confirmed',
        'is_approved', 'reason', 'data', 'terms_link',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trades';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for character creation.
     *
     * @var array
     */
    public static $createRules = [
        'status'     => 'required|in:Open,Pending,Completed,Rejected,Canceled,Proposal',
        'terms_link' => 'nullable|url',
    ];

    /**
     * Validation rules for character updating.
     *
     * @var array
     */
    public static $updateRules = [
        'status'     => 'required|in:Open,Pending,Completed,Rejected,Canceled,Proposal',
        'terms_link' => 'nullable|url',
    ];

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

    /**
     * Scope a query to only include finalised trades.
     *
     * @param mixed $query
     */
    public function scopeCompleted($query) {
        return $query->where('status', 'Completed')->orWhere('status', 'Rejected');
    }

    /**
     * Scope a query to order by the newest trades.
     *
     * @param mixed $query
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to order by the oldest trades.
     *
     * @param mixed $query
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id', 'ASC');
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
     * Gets the URL of the trade.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('trades/'.$this->id);
    }

    /**
     * Gets the stacks of the trade keyed by sender and recipient.
     *
     * @return array
     */
    public function getStacksAttribute() {
        $stacks = [];
        foreach ($this->data as $side => $assets) {
            if (isset($assets['user_items'])) {
                $user_items = UserItem::with('item')->find(array_keys($assets['user_items']));
                $items = $user_items->map(function ($user_item) use ($assets) {
                    $user_item['quantity'] = $assets['user_items'][$user_item->id];

                    return $user_item;
                });
                $stacks[$side] = $items->groupBy('item_id');
            }
        }

        return $stacks;
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
