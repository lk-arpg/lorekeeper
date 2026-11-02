<?php

namespace App\Models\Limit;

use App\Models\Model;

class Limit extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_model', 'object_id', 'limit_type', 'limit_id', 'quantity', 'debit', 'is_unlocked', 'is_auto_unlocked',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'limits';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'limit',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the object that this limit is attached to as something that morphMany will accept.
     */
    public function limitable() {
        return $this->morphTo('limitable', 'object_model', 'object_id');
    }

    /**
     * Get the object that this limit is attached to.
     */
    public function object() {
        return $this->morphTo('object', 'object_model', 'object_id');
    }

    /**
     * gets the limit of this ... limit.
     */
    public function limit() {
        return $this->morphTo('limit', 'limit_type', 'limit_id');

        /*
         * If you have specific logic per limit_type (such as, if you have multiple limits that share the same model),
         * you can use ->constrain([]) to handle that extra logic.
         *
         * For example, if you were to have a generic "Model" that needs to be separated between character and user:
         *
         * ->constrain([
         *     Model::class => function ($query) {
         *         if ($this->limit_type === 'user_model') {
         *             $query->where('type', 'user');
         *         } elseif ($this->limit_type === 'character_model') {
         *             $query->where('type', 'character');
         *         }
         *     }
         * ]);
         *
         */
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks if a certain object has any limits.
     * This is kept for backwards compatibility, and forwards to the helper function.
     *
     * @param mixed $object
     */
    public static function hasLimits($object) {
        return hasLimits($object);
    }

    /**
     * Get the limits of a certain object.
     * This is kept for backwards compatibility, and forwards to the helper function.
     *
     * @param mixed $object
     */
    public static function getLimits($object) {
        return getLimits($object);
    }

    /**
     * Checks if a user has unlocked this.
     *
     * @param mixed $user
     */
    public function isUnlocked($user) {
        if (!$user) {
            return false;
        }

        return $this->is_unlocked && $user->unlockedLimits->where('object_model', $this->object_model)->where('object_id', $this->object_id)->count();
    }
}
