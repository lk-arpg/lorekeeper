<?php

namespace App\Models\User;

use App\Models\Item\Item;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserItem extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'item_id', 'user_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_items';

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

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the item associated with this item stack.
     */
    public function item() {
        return $this->belongsTo(Item::class);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Checks if the stack is transferrable.
     *
     * @return array
     */
    public function getIsTransferrableAttribute() {
        if ((!isset($this->data['disallow_transfer']) || !$this->data['disallow_transfer']) && $this->item->allow_transfer) {
            return true;
        }

        return false;
    }

    /**
     * Gets the available quantity of the stack.
     *
     * @return int
     */
    public function getAvailableQuantityAttribute() {
        return $this->count - $this->trade_count - $this->update_count - $this->submission_count;
    }

    /**
     * Gets the stack's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'user_items';
    }

    /**
     * Returns string stating amount held elsewhere.
     *
     * @param mixed $tradeCount
     * @param mixed $updateCount
     * @param mixed $submissionCount
     *
     * @return string
     */
    public function getOthers($tradeCount = 0, $updateCount = 0, $submissionCount = 0) {
        return $this->getHeldString($this->trade_count - $tradeCount, $this->update_count - $updateCount, $this->submission_count - $submissionCount);
    }

    /**
     * Gets the available quantity based on input context (either trade count or update count).
     *
     * @param mixed $count
     *
     * @return int
     */
    public function getAvailableContextQuantity($count) {
        return $this->getAvailableQuantityAttribute() + $count;
    }

    /**
     * Construct string stating held items.
     *
     * @param mixed $tradeCount
     * @param mixed $updateCount
     * @param mixed $submissionCount
     *
     * @return string
     */
    private function getHeldString($tradeCount, $updateCount, $submissionCount) {
        if (!$tradeCount && !$updateCount && !$submissionCount) {
            return null;
        }
        $held = [];
        if ($tradeCount) {
            array_push($held, $tradeCount.' held in Trades');
        }
        if ($updateCount) {
            array_push($held, $updateCount.' held in Design Updates');
        }
        if ($submissionCount) {
            array_push($held, $submissionCount.' held in Submissions');
        }

        return '('.implode(', ', $held).')';
    }
}
