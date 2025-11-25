<?php

namespace App\Services;

use App\Models\Limit\DynamicLimit;
use App\Models\Limit\Limit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LimitService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Limit Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of limits on objects
    |
    */

    /**********************************************************************************************

        LIMITS

    **********************************************************************************************/

    /**
     * edits an limits on an object.
     *
     * @param mixed     $data
     * @param bool|true $log
     * @param mixed     $object_model
     * @param mixed     $object_id
     */
    public function editLimits($object_model, $object_id, $data, $log = true) {
        DB::beginTransaction();

        try {
            // first delete all limits for the object
            $object = $object_model::find($object_id);
            if (!$object) {
                throw new \Exception('Object not found.');
            }

            $limits = hasLimits($object) ? getLimits($object) : [];
            if (count($limits) > 0) {
                $limits->each(function ($limit) {
                    $limit->delete();
                });
            }
            if (count($limits) > 0) {
                flash('Deleted '.count($limits).' old limits.')->success();
            }

            if (isset($data['limit_type'])) {
                foreach ($data['limit_type'] as $key => $type) {
                    $limit = new Limit([
                        'object_model'     => $object_model,
                        'object_id'        => $object_id,
                        'limit_type'       => $data['limit_type'][$key],
                        'limit_id'         => $data['limit_id'][$key],
                        'quantity'         => $data['quantity'][$key],
                        'debit'            => $data['debit'][$key] == 'no' ? 0 : 1,
                        'is_unlocked'      => $data['is_unlocked'] == 'no' ? 0 : 1,
                        'is_auto_unlocked' => $data['is_auto_unlocked'] == 'no' ? 0 : 1,
                    ]);

                    if (!$limit->save()) {
                        throw new \Exception('Failed to save limit.');
                    }
                }
            }

            // log the action
            if ($log && !$this->logAdminAction(Auth::user(), 'Edited Limits', 'Edited '.$object->displayName.' limits')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Unlocks the limits for an object.
     *
     * @param mixed $object
     *
     * @return bool
     */
    public function unlockLimits($object) {
        DB::beginTransaction();

        try {
            $service = new LimitManager;
            if (!$service->checkLimits($object, true)) {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
                throw new \Exception('Failed to unlock limits.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        DYNAMIC LIMITS

    **********************************************************************************************/

    /**
     * Creates a new limit.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Limit
     */
    public function createLimit($data, $user) {
        DB::beginTransaction();

        try {
            $data['description'] = isset($data['description']) ? parse($data['description']) : null;
            $data['evaluation'] = str_replace('\\', '\\\\', $data['evaluation']);

            $limit = DynamicLimit::create($data);

            return $this->commitReturn($limit);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a limit.
     *
     * @param Limit                 $limit
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Limit
     */
    public function updateLimit($limit, $data, $user) {
        DB::beginTransaction();

        try {
            if (DynamicLimit::where('name', $data['name'])->where('id', '!=', $limit->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data['description'] = isset($data['description']) ? parse($data['description']) : null;
            $data['evaluation'] = str_replace('\\', '\\\\', $data['evaluation']);

            $limit->update($data);

            return $this->commitReturn($limit);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a limit.
     *
     * @param Limit $limit
     *
     * @return bool
     */
    public function deleteLimit($limit) {
        DB::beginTransaction();

        try {
            if (Limit::where('limit_type', 'dynamic')->where('limit_id', $limit->id)->exists()) {
                throw new \Exception('This limit is currently in use and cannot be deleted.');
            }
            $limit->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
