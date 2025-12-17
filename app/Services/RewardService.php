<?php

namespace App\Services;

use App\Models\Reward\Reward;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RewardService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Reward Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of rewards on objects
    |
    */

    /**********************************************************************************************

        REWARDS

    **********************************************************************************************/

    /**
     * Processes user input for creating/updating prompt rewards.
     *
     * @param mixed $data
     * @param mixed $object_model
     * @param mixed $object_id
     * @param mixed $data
     * @param bool  $log
     */
    public function populateRewards($object_model, $object_id, $data, $log = true) {
        DB::beginTransaction();

        try {
            // first delete all rewards for the object
            $object = $object_model::find($object_id);
            if (!$object) {
                throw new \Exception('Object not found.');
            }

            $rewards = hasRewards($object) ? getRewards($object) : [];
            if (count($rewards) > 0) {
                $rewards->each(function ($reward) {
                    $reward->delete();
                });
            }

            // build data object
            $rewardableData = [];
            if (isset($data['data'])) {
                foreach ($data['data'] as $name => $values) {
                    if (is_array($values)) {
                        foreach ($values as $k => $v) {
                            $rewardableData[$k][$name] = $v;
                        }
                    } else {
                        $rewardableData[$name] = $values;
                    }
                }
            }

            if (isset($data['rewardable_type'])) {
                foreach ($data['rewardable_type'] as $key => $type) {
                    $rewardData = [
                        'object_model'         => $object_model,
                        'object_id'            => $object_id,
                        'rewardable_recipient' => $data['rewardable_recipient'][$key] ?? 'User',
                        'rewardable_type'      => $data['rewardable_type'][$key],
                        'rewardable_id'        => $data['rewardable_id'][$key],
                        'quantity'             => $data['quantity'][$key],
                        'data'                 => $rewardableData[$key] ?? (count($rewardableData) > 0 ? $rewardableData : null),
                    ];

                    $validator = Validator::make($rewardData, Reward::$createRules);

                    if ($validator->fails()) {
                        // Unfortunately $validator->errors() gets eaten here if we try to save it to the session/flash them
                        // so we just have to return a generic error message
                        // However, uncommenting the below will send the validator errors to the logs
                        // \Log::debug($validator->errors());
                        throw new \Exception('Reward data validation failed.');
                    }

                    $reward = Reward::create($rewardData);

                    if (!$reward->save()) {
                        throw new \Exception('Failed to save reward.');
                    }
                }
            }

            // log the action
            if ($log && !$this->logAdminAction(Auth::user(), 'Edited Rewards', 'Edited '.$object->displayName.' rewards')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
