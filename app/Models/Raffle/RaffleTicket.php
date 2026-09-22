<?php

namespace App\Models\Raffle;

use App\Models\Model;
use App\Models\User\User;

class RaffleTicket extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'raffle_id', 'position', 'created_at', 'alias',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raffle_tickets';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'user_id.*'      => 'required_without:alias.*',
        'alias.*'        => 'required_without:user_id.*',
        'ticket_count.*' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the raffle this ticket is for.
     */
    public function raffle() {
        return $this->belongsTo(Raffle::class);
    }

    /**
     * Get the user who owns the raffle ticket.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include the winning tickets in order of drawing.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWinners($query) {
        $query->whereNotNull('position')->orderBy('position');
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Display the ticket holder's name.
     * If the owner is not a registered user on the site, this displays the ticket holder's dA name.
     *
     * @return string
     */
    public function getDisplayHolderNameAttribute() {
        if ($this->user_id) {
            return $this->user->displayName;
        }

        return $this->alias.' (Off-site user)';
    }

    /**
     * Returns the identifier of the ticket.
     */
    public function getIdentifierAttribute() {
        if ($this->user_id) {
            return $this->user->id;
        }

        return $this->alias;
    }
}
