<?php

namespace App\Models;

use App\Models\Prompt\Prompt;
use App\Models\Submission\Submission;
use App\Services\LimitManager;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel {
    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    protected static function boot() {
        parent::boot();

        $service = new LimitManager;
        $object = null;
        static::creating(function ($model) use ($service, $object) {
            switch (get_class($model)) {
                case Submission::class:
                    if ($model->status == 'Pending') {
                        return true;
                    }
                    $object = Prompt::find($model->prompt_id);
                    break;
            }

            if (!$object) {
                return true;
            }

            if (!$service->checkLimits($object)) {
                throw new \Exception($service->errors()->getMessages()['error'][0]);
            }
        });

        static::updating(function ($model) use ($service, $object) {
            switch (get_class($model)) {
                case Submission::class:
                    if ($model->status != 'Pending' || $model->staff_comments || $model->staff_id) {
                        return true;
                    }
                    $object = Prompt::find($model->prompt_id);
                    break;
            }

            if (!$object) {
                return true;
            }

            if (!$service->checkLimits($object)) {
                throw new \Exception($service->errors()->getMessages()['error'][0]);
            }
        });
    }
}
