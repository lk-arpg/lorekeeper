<?php

namespace App\Models\Raffle;

use App\Models\Model;

class Raffle extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_active', 'winner_count', 'group_id', 'order', 'allow_entry', 'is_fto', 'unordered', 'end_at', 'roll_on_end', 'ticket_cap',
        'description', 'parsed_description',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raffles';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rolled_at' => 'datetime',
        'end_at'    => 'datetime',
    ];

    /**
     * Accessors to append to the model.
     *
     * @var array
     */
    public $appends = ['name_with_group'];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the raffle tickets attached to this raffle.
     */
    public function tickets() {
        return $this->hasMany(RaffleTicket::class);
    }

    /**
     * Get the group that this raffle belongs to.
     */
    public function group() {
        return $this->belongsTo(RaffleGroup::class, 'group_id');
    }

    /**
     * Gets the logs associated with this raffle.
     */
    public function logs() {
        return $this->hasMany(RaffleLog::class);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the raffle's name, linked to the raffle page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return $this->displayName();
    }

    /**
     * Get the name of the raffle, including group name if there is one.
     *
     * @return string
     */
    public function getNameWithGroupAttribute() {
        return ($this->group_id ? '['.$this->group->name.'] ' : '').$this->name;
    }

    /**
     * Gets the raffle's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'raffle_tickets';
    }

    /**
     * Gets the raffle's url.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('raffles/view/'.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/raffles'); // Raffles are edited via a modal so don't have a unique raffle edit page
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'manage_raffles';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Displays the raffle's name, linked to the raffle page.
     *
     * @param mixed $asReward
     *
     * @return string
     */
    public function displayName($asReward = true) {
        return '<a href="'.$this->url.'" class="display-raffle">'.$this->name.($asReward ? ' (Raffle Ticket)' : '').'</a>';
    }
}
