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
     * Get the object that this reward is attached to.
     */
    public function object() {
        return $this->morphTo('object', 'object_model', 'object_id');
    }

    /**
     * Get the reward associated with this entry.
     */
    public function reward() {
        $model = getAssetModelString(strtolower($this->rewardable_type));

        if (!class_exists($model)) {
            // Laravel requires a relationship instance to be returned (cannot return null), so returning one that doesn't exist here.
            return $this->belongsTo(self::class, 'id', 'object_id')->whereNull('object_id');
        }

        return $this->belongsTo($model, 'rewardable_id');
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * checks if a certain object has any rewards.
     *
     * @param mixed $object
     */
    public static function hasRewards($object) {
        return self::where('object_model', get_class($object))->where('object_id', $object->id)->exists();
    }

    /**
     * get the rewards of a certain object.
     *
     * @param mixed $object
     */
    public static function getRewards($object) {
        return self::where('object_model', get_class($object))->where('object_id', $object->id)->get();
    }
}
