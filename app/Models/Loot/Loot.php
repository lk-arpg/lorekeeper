<?php

namespace App\Models\Loot;

use App\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Loot extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loot_table_id', 'rewardable_type', 'rewardable_id',
        'quantity', 'weight', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loots';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'rewardable_type' => 'nullable',
        'rewardable_id'   => 'nullable',
        'quantity'        => 'required|integer|min:1',
        'weight'          => 'required|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'rewardable_type' => 'nullable',
        'rewardable_id'   => 'nullable',
        'quantity'        => 'required|integer|min:1',
        'weight'          => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the reward attached to the loot entry.
     */
    public function reward() {
        return $this->morphTo('reward', 'rewardable_type', 'rewardable_id');
        // 'None' is implicitly handled by morphTo.
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Override the rewardable type value
     * we get None for site functions, but null for database relationship.
     */
    protected function rewardableType(): Attribute {
        return Attribute::make(
            get: fn (?string $value) => $value ? $value : 'None',
            set: fn (?string $value) => $value,
        );
    }
}
