<?php

namespace App\Models\Raffle;

use App\Models\Model;
use App\Models\User\User;

class RaffleLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'raffle_id', 'reason', 'ticket_id', 'type',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raffle_logs';

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
     * Get the raffle this log is for.
     */
    public function raffle() {
        return $this->belongsTo(Raffle::class);
    }

    /**
     * Get the user who made the raffle log.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the ticket this log is for.
     */
    public function ticket() {
        return $this->belongsTo(RaffleTicket::class, 'ticket_id');
    }
}
