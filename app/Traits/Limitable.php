<?php

namespace App\Traits;

use App\Models\Limit\Limit;

/**
 * Add this trait to any model that you want to have limits.
 */
trait Limitable {
    public function limits() {
        return $this->morphMany(Limit::class, 'limitable', 'object_model', 'object_id');
    }

    public function getHasLimitsAttribute() {
        return $this->limits->count() !== 0;
    }
}
