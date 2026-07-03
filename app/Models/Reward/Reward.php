<?php

namespace App\Models\Reward;

use App\Models\Model;

class Reward extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_id', 'object_model', 'rewardable_recipient', 'rewardable_id', 'rewardable_type', 'quantity', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rewards';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'reward',
    ];

    /**
     * Validation rules for reward creation.
     *
     * @var array
     */
    public static $createRules = [
        'object_id'            => 'required',
        'object_model'         => 'required',
        'rewardable_recipient' => 'required',
        'rewardable_id'        => 'nullable',
        'rewardable_type'      => 'required',
        'quantity'             => 'required',
        'data'                 => 'nullable',
    ];

    /**
     * Validation rules for reward updating.
     *
     * @var array
     */
    public static $updateRules = [
        'object_id'            => 'required',
        'object_model'         => 'required',
        'rewardable_recipient' => 'required',
        'rewardable_id'        => 'nullable',
        'rewardable_type'      => 'required',
        'quantity'             => 'required',
        'data'                 => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the object that this reward is attached to as something that morphMany will accept.
     */
    public function rewardable() {
        return $this->morphTo('rewardable', 'object_model', 'object_id');
    }

    /**
     * Get the object that this reward is attached to.
     */
    public function object() {
        return $this->morphTo('object', 'object_model', 'object_id');
    }

    /**
     * Get the reward associated with this entry.
     */
    public function reward() {
        return $this->morphTo('reward', 'rewardable_type', 'rewardable_id');
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks if a certain object has any rewards.
     * This is kept for backwards compatibility, and forwards to the helper function.
     *
     * @param mixed $object
     */
    public static function hasRewards($object) {
        return hasRewards($object);
    }

    /**
     * Get the rewards of a certain object.
     * This is kept for backwards compatibility, and forwards to the helper function.
     *
     * @param mixed $object
     */
    public static function getRewards($object) {
        return getRewards($object);
    }
}
