<?php

namespace App\Traits;

use App\Models\Reward\Reward;

/**
 * Add this trait to any model that you want to have limits.
 */
trait Rewardable {
    public function rewards() {
        return $this->morphMany(Reward::class, 'rewardable', 'object_model', 'object_id');
    }

    public function getHasRewardsAttribute() {
        return $this->rewards->count() !== 0;
    }
}
