<?php

namespace App\Services;

use App\Models\Limit\Limit;
use App\Models\Submission\Submission;
use App\Models\User\UserItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LimitManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Limit Manager
    |--------------------------------------------------------------------------
    |
    | Handles the checking of limits on objects
    |
    */

    /**********************************************************************************************

        LIMITS

    **********************************************************************************************/

    /**
     * checks all limits on an object.
     *
     * @param mixed $object
     * @param mixed $is_unlock
     */
    public function checkLimits($object, $is_unlock = false) {
        try {
            $user = Auth::user();

            $limits = hasLimits($object) ? getLimits($object) : [];
            if (!count($limits)) {
                return true;
            }

            if ($limits->first()->is_unlocked) {
                if (!$user) {
                    throw new \Exception('You must be logged in to complete this action.');
                }

                if ($user->unlockedLimits()->where('object_model', get_class($object))->where('object_id', $object->id)->exists()) {
                    return true;
                }
            }

            // if the limit is not unlocked, check if it is auto unlocked
            if (!$is_unlock && !$limits->first()->is_auto_unlocked) {
                throw new \Exception(($limits->first()->object->displayName ?? $limits->first()->object->name).' requires manual unlocking!');
            }

            $plucked_stacks = [];
            foreach ($limits as $limit) {
                switch ($limit->limit_type) {
                    case 'prompt':
                        // check at least quantity of prompts has been approved
                        if (Submission::where('user_id', $user->id)->where('status', 'Approved')->where('prompt_id', $limit->limit_id)->count() < $limit->quantity) {
                            throw new \Exception('You have not completed the prompt '.$limit->limit->displayName.' enough times to complete this action.');
                        }
                        break;
                    case 'item':
                        if (!$user->items()->where('item_id', $limit->limit_id)->sum('count') >= $limit->quantity) {
                            throw new \Exception('You do not have enough of the item '.$limit->object->name.' to complete this action.');
                        }

                        if ($limit->debit) {
                            $stacks = UserItem::where('user_id', $user->id)->where('item_id', $limit->limit_id)->orderBy('count', 'asc')->get(); // asc because pop() removes from the end

                            $count = $limit->quantity;
                            while ($count > 0) {
                                $stack = $stacks->pop();
                                $quantity = $stack->count >= $count ? $count : $stack->count;
                                $count -= $quantity;
                                $plucked_stacks[$stack->id] = $quantity;
                            }
                        }
                        break;
                    case 'currency':
                        if (DB::table('user_currencies')->where('user_id', $user->id)->where('currency_id', $limit->limit_id)->value('quantity') < $limit->quantity) {
                            throw new \Exception('You do not have enough '.$limit->limit->displayName.' to complete this action.');
                        }

                        if ($limit->debit) {
                            $service = new CurrencyManager;
                            if (!$service->debitCurrency($user, null, 'Limit Requirements', 'Used in '.$limit->object->displayName.' limit requirements.', $limit->limit, $limit->quantity)) {
                                foreach ($service->errors()->getMessages()['error'] as $error) {
                                    flash($error)->error();
                                }
                                throw new \Exception('Currency could not be removed.');
                            }
                        }
                        break;
                    case 'dynamic':
                        if (!$this->checkDynamicLimit($limit, $user)) {
                            throw new \Exception('You do not meet the requirements to complete this action.');
                        }
                        break;
                }
            }

            if (count($plucked_stacks)) {
                $inventoryManager = new InventoryManager;
                $type = 'Limit Requirements';
                $data = [
                    'data' => 'Used in '.($limit->object->displayName ?? $limit->object->name).'\'s limit requirements.',
                ];

                foreach ($plucked_stacks as $id=>$quantity) {
                    $stack = UserItem::find($id);
                    if (!$inventoryManager->debitStack($user, $type, $data, $stack, $quantity)) {
                        throw new \Exception('Items could not be removed.');
                    }
                }
            }

            if ($limits->first()->is_unlocked && $limits->first()->is_auto_unlocked || $is_unlock) {
                $user->unlockedLimits()->create([
                    'object_model' => get_class($object),
                    'object_id'    => $object->id,
                ]);
            } elseif (!$is_unlock && $limits->first()->is_unlocked && !$limits->first()->is_auto_unlocked) {
                throw new \Exception(($limits->first()->object->displayName ?? $limits->first()->object->name).' requires manual unlocking!');
            }

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }

    /**
     * checks a dynamic limit.
     *
     * @param mixed $limit
     * @param mixed $user
     */
    private function checkDynamicLimit($limit, $user) {
        try {
            return $limit->limit->evaluate();
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }
}
