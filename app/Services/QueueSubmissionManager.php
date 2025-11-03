<?php
namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Queue\Queue;
use App\Models\Queue\QueueSubmission;
use App\Models\Queue\QueueSubmissionCharacter;
use App\Models\Raffle\Raffle;
use App\Models\User\User;
use App\Models\User\UserItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class QueueSubmissionManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Submission Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of submission data.
    |
    */

    /**
     * Creates a new submission.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $isDraft
     *
     * @return mixed
     */
    public function createSubmission($queue, $data, $user, $isDraft = false)
    {
        DB::beginTransaction();

        try {
            // 1. check that the queue can be submitted at this time
            // 2. check that the characters selected exist (are visible too)
            // 3. check that the currencies selected can be attached to characters
            if (! Settings::get('is_queue_open')) {
                throw new \Exception('The queue is closed for submissions.');
            }

            if ($queue->staff_only && ! $user->isStaff) {
                throw new \Exception('This queue may only be submitted to by staff members.');
            }

            if (! $queue->checkConcurrent($user)) {
                throw new \Exception("This queue does not permit you to submit more submissions while you have " . $queue->limit_concurrent . " of them of them pending or in draft at the same time. Please wait for your submissions to be processed before trying to submit again.");
            }

            if ($queue->limit) {
                if (! $queue->checkLimit($user)) {
                    throw new \Exception("You have already submitted to this queue the maximum number of times.");
                }
            }

            if (isset($data['comments']) && $data['comments']) {
                $data['parsed_comments'] = parse($data['comments']);
            } else {
                $data['parsed_comments'] = null;
            }

            // Create the submission itself.
            $submission = QueueSubmission::create([
                'user_id'         => $user->id,
                'status'          => $isDraft ? 'Draft' : 'Pending',
                'comments'        => $data['comments'],
                'parsed_comments' => $data['parsed_comments'],
                'data'            => null,
                'queue_id'        => $queue->id,
            ]);

            $assets       = $this->createUserAttachments($submission, $data, $user);
            $queueRewards = $assets['queueRewards'];
            if ($queue->configSet('item_consume')) {
                // Set items that have been attached.
                $userAssets = $assets['userAssets'];
            }

            //carry out the initial processes when submitting the queue's form
            $service = $queue->service;

            if ($queue->configSet('character_submit')) {
                if (! $this->createCharacterAttachments($submission, $data, $service, $user)) {
                    throw new \Exception("Failed to handle submission characters.");
                }
            }
            if (method_exists($queue->service, 'submit')) {
                if (! $service->submit($queue, $data, $user, $submission)) {
                    foreach ($service->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }
                    throw new \Exception("Failed to handle submission.");
                }
            }

            $submission->update([
                'data' => [
                    'user'    => $queue->configSet('item_consume') ? Arr::only(getDataReadyAssets($userAssets), ['user_items', 'currencies']) : null,
                    'queue'   => method_exists($queue->service, 'processSubmit') ? $queue->service->processSubmit($queue, $data, $user, $submission) : null,
                    'rewards' => getDataReadyAssets($queueRewards),
                ],
            ]);

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits an existing submission.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $submission
     * @param mixed $isSubmit
     *
     * @return mixed
     */
    public function editSubmission($submission, $data, $user, $isSubmit = false)
    {
        DB::beginTransaction();

        try {
            // 1. check that the queue can be submitted at this time
            // 2. check that the characters selected exist (are visible too)
            // 3. check that the currencies selected can be attached to characters
            if (! Settings::get('is_queue_open')) {
                throw new \Exception('The queue is closed for submissions.');
            }
            $queue = $submission->queue;
            if (! $queue) {
                throw new \Exception('Invalid queue selected.');
            }

            // First, return all items and currency applied.
            // Also, as this is an edit, delete all attached characters to be re-applied later.
            if ($queue->configSet('item_consume')) {
                $this->removeAttachments($submission);
            }

            if ($queue->configSet('character_submit')) {
                QueueSubmissionCharacter::where('queue_submission_id', $submission->id)->delete();
            }

            if ($isSubmit) {
                $submission->update(['status' => 'Pending', 'submitted_at' => Carbon::now()]);
            }

            $assets       = $this->createUserAttachments($submission, $data, $user);
            $queueRewards = $assets['queueRewards'];
            if ($queue->configSet('item_consume')) {
                // Then, re-attach everything fresh.
                $userAssets = $assets['userAssets'];
            }

            $service = $queue->service;

            if ($queue->configSet('character_submit')) {
                if (! $this->createCharacterAttachments($submission, $data, $service, $user)) {
                    throw new \Exception("Failed to handle submission characters.");
                }
            }
            if (method_exists($queue->service, 'submit')) {
                if (! $service->submit($queue, $data, $user, $submission)) {
                    foreach ($service->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }
                    throw new \Exception("Failed to handle submission.");
                }
            }

            if (isset($data['comments']) && $data['comments']) {
                $data['parsed_comments'] = parse($data['comments']);
            } else {
                $data['parsed_comments'] = null;
            }

            // Modify submission
            $submission->update([
                'updated_at'      => Carbon::now(),
                'comments'        => $data['comments'],
                'parsed_comments' => $data['parsed_comments'],
                'queue_id'        => $queue->id,
                'data'            => [
                    'user'    => $queue->configSet('item_consume') ? Arr::only(getDataReadyAssets($userAssets), ['user_items', 'currencies']) : null,
                    'queue'   => method_exists($queue->service, 'processSubmit') ? $queue->service->processSubmit($queue, $data, $user, $submission) : null,
                    'rewards' => getDataReadyAssets($queueRewards),
                ],
            ]);

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Cancels a submission.
     *
     * @param mixed $data the submission data
     * @param mixed $user the user performing the cancellation
     */
    public function cancelSubmission($data, $user)
    {
        DB::beginTransaction();

        try {
            // 1. check that the submission exists
            // 2. check that the submission is pending
            if (! isset($data['submission'])) {
                $submission = QueueSubmission::where('status', 'Pending')->where('id', $data['id'])->first();
            } elseif ($data['submission']->status == 'Pending') {
                $submission = $data['submission'];
            } else {
                $submission = null;
            }
            if (! $submission) {
                throw new \Exception('Invalid submission.');
            }

            // Set staff comments
            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

            $assets = $submission->data;
            if ($submission->queue->configSet('item_consume')) {
                $userAssets = $assets['user'];
            }
            $qAssets = $assets['queue'];
            // Remove queue-only rewards
            $queueRewards = $this->removeQueueAttachments($submission);

            if ($user->id != $submission->user_id) {
                // The only things we need to set are:
                // 1. staff comment
                // 2. staff ID
                // 3. status
                $submission->update([
                    'staff_comments'        => $data['staff_comments'],
                    'parsed_staff_comments' => $data['parsed_staff_comments'],
                    'updated_at'            => Carbon::now(),
                    'staff_id'              => $user->id,
                    'status'                => 'Draft',
                    'data'                  => [
                        'user'    => $submission->queue->configSet('item_consume') ? $userAssets : null,
                        'queue'   => $qAssets,
                        'rewards' => getDataReadyAssets($queueRewards),
                    ],
                ]);

                Notifications::create('QUEUE_SUBMISSION_CANCELLED', $submission->user, [
                    'queue_name'   => $submission->queue->name,
                    'staff_url'     => $user->url,
                    'staff_name'    => $user->name,
                    'submission_id' => $submission->id,
                ]);
            } else {
                // This is when a user cancels their own submission back into draft form
                $submission->update([
                    'status'     => 'Draft',
                    'updated_at' => Carbon::now(),
                    'data'       => [
                        'user'    => $submission->queue->configSet('item_consume') ? $userAssets : null,
                        'queue'   => $qAssets,
                        'rewards' => getDataReadyAssets($queueRewards),
                    ],
                ]);
            }

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Rejects a submission.
     *
     * @param array $data
     * @param User  $user
     *
     * @return mixed
     */
    public function rejectSubmission($data, $user)
    {
        DB::beginTransaction();

        try {

            // 1. check that the submission exists
            // 2. check that the submission is pending
            if (! isset($data['submission'])) {
                $submission = QueueSubmission::where('status', 'Pending')->where('id', $data['id'])->first();
            } elseif ($data['submission']->status == 'Pending') {
                $submission = $data['submission'];
            } else {
                $submission = null;
            }
            if (! $submission) {
                throw new \Exception('Invalid submission.');
            }

            $queue = $submission->queue;

            if ($queue->configSet('item_consume')) {
                // Return all items and currency applied.
                $this->removeAttachments($submission);
            }

            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

            // The only things we need to set are:
            // 1. staff comment
            // 2. staff ID
            // 3. status
            $submission->update([
                'staff_comments'        => $data['staff_comments'],
                'parsed_staff_comments' => $data['parsed_staff_comments'],
                'staff_id'              => $user->id,
                'status'                => 'Rejected',
            ]);

            Notifications::create('QUEUE_SUBMISSION_REJECTED', $submission->user, [
                'queue_name'   => $submission->queue->name,
                'staff_url'     => $user->url,
                'staff_name'    => $user->name,
                'submission_id' => $submission->id,
            ]);

            /*
                if (!$this->logAdminAction($user, 'Submission Rejected', 'Rejected submission <a href="'.$submission->viewurl.'">#'.$submission->id.'</a>')) {
                    throw new \Exception('Failed to log admin action.');
                }
            */

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approves a submission.
     *
     * @param array $data
     * @param User  $user
     *
     * @return mixed
     */
    public function approveSubmission($data, $user)
    {
        DB::beginTransaction();

        try {
            // 1. check that the submission exists
            // 2. check that the submission is pending
            $submission = QueueSubmission::where('status', 'Pending')->where('id', $data['id'])->first();
            if (! $submission) {
                throw new \Exception('Invalid submission.');
            }

            $queue   = $submission->queue;
            $service = $queue->service;

            if ($queue->configSet('item_consume')) {
                // Remove any added items, hold counts, and add logs
                $addonData        = $submission->data['user'];
                $inventoryManager = new InventoryManager;
                if (isset($addonData['user_items'])) {
                    $stacks = $addonData['user_items'];
                    foreach ($addonData['user_items'] as $userItemId => $quantity) {
                        $userItemRow = UserItem::find($userItemId);
                        if (! $userItemRow) {
                            throw new \Exception('Cannot return an invalid item. (' . $userItemId . ')');
                        }
                        if ($userItemRow->submission_count < $quantity) {
                            throw new \Exception('Cannot return more items than was held. (' . $userItemId . ')');
                        }
                        $userItemRow->submission_count -= $quantity;
                        $userItemRow->save();
                    }

                    // Workaround for user not being unset after inventory shuffling, preventing proper staff ID assignment
                    $staff = $user;

                    foreach ($stacks as $stackId => $quantity) {
                        $stack = UserItem::find($stackId);
                        $user  = User::find($submission->user_id);
                        if (! $inventoryManager->debitStack($user, 'Queue Submission Approved', ['data' => 'Item used in submission (<a href="' . $submission->viewUrl . '">#' . $submission->id . '</a>)'], $stack, $quantity)) {
                            throw new \Exception('Failed to create log for item stack.');
                        }
                    }

                    // Set user back to the processing staff member, now that addons have been properly processed.
                    $user = $staff;
                }

                // Log currency removal, etc.
                $currencyManager = new CurrencyManager;
                if (isset($addonData['currencies']) && $addonData['currencies']) {
                    foreach ($addonData['currencies'] as $currencyId => $quantity) {
                        $currency = Currency::find($currencyId);
                        if (! $currencyManager->createLog(
                            $submission->user_id,
                            'User',
                            null,
                            null,
                            'Queue Submission Approved',
                            'Used in submission (<a href="' . $submission->viewUrl . '">#' . $submission->id . '</a>)',
                            $currencyId,
                            $quantity
                        )) {
                            throw new \Exception('Failed to create currency log.');
                        }
                    }
                }
            }

            // Get the updated set of rewards
            $rewards = $this->processRewards($data, false, true);

            // Logging data
            $queueLogType = 'Queue Rewards';
            $queueData    = [
                'data' => 'Received rewards for submission (<a href="' . $submission->viewUrl . '">#' . $submission->id . '</a>)',
            ];

            // Distribute user rewards
            if (! $rewards = fillUserAssets($rewards, $user, $submission->user, $queueLogType, $queueData)) {
                throw new \Exception('Failed to distribute rewards to user.');
            }

            if ($queue->configSet('character_submit')) {

                // The character identification comes in both the slug field and as character IDs
                // that key the reward ID/quantity arrays.
                // We'll need to match characters to the rewards for them.
                // First, check if the characters are accessible to begin with.
                if (isset($data['slug'])) {
                    $characters = Character::myo(0)->visible()->whereIn('slug', $data['slug'])->get();
                    if (count($characters) != count($data['slug'])) {
                        throw new \Exception('One or more of the selected characters do not exist.');
                    }
                } else {
                    $characters = [];
                }

                //process any relevant data
                if (method_exists($service, 'processCharacters')) {

                    if (! $chardata = $service->processCharacters($submission->queue, $data, $submission)) {
                        foreach ($service->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }
                        throw new \Exception("Failed to handle submission characters.");
                    }
                }

                // We're going to remove all characters from the submission and reattach them with the updated data
                $submission->characters()->delete();

                // Distribute character rewards
                foreach ($characters as $c) {

                    if (method_exists($service, 'processCharacterAttachments')) {
                        if (! $assets = $service->processCharacterAttachments($submission->queue, $data + ['character_id' => $c->id], $submission)) {
                            foreach ($service->errors()->getMessages()['error'] as $error) {
                                flash($error)->error();
                            }
                            throw new \Exception("Failed to handle submission characters.");
                        }
                    }

                    QueueSubmissionCharacter::create([
                        'character_id'        => $c->id,
                        'queue_submission_id' => $submission->id,
                        'data'                => method_exists($service, 'finalizeCharacterAttachments') ? $service->finalizeCharacterAttachments($submission->queue, $data + ['character_id' => $c->id], $submission, $user) : null,
                    ]);
                }
            }

            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

            //carry out approval

            if (method_exists($queue->service, 'approve')) {
                if (! $service->approve($queue, $data, $user, $submission)) {
                    foreach ($service->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }
                    throw new \Exception("Failed to handle submission.");
                }
            }

            //back up the data cus i realized it gets nuked on approval
            $savedData = $submission->data['queue'];

            // Finally, set:
            // 1. staff comments
            // 2. staff ID
            // 3. status
            // 4. final rewards
            $submission->update([
                'staff_comments'        => $data['staff_comments'],
                'parsed_staff_comments' => $data['parsed_staff_comments'],
                'staff_id'              => $user->id,
                'status'                => 'Approved',
                'data'                  => [
                    'user'    => $queue->configSet('item_consume') ? $addonData : null,
                    'queue'   => method_exists($queue->service, 'processApprove') ? $queue->service->processApprove($queue, $data, $user, $submission) : $savedData,
                    'rewards' => getDataReadyAssets($rewards),
                ], // list of rewards
            ]);

            Notifications::create('QUEUE_SUBMISSION_APPROVED', $submission->user, [
                'queue_name'   => $submission->queue->name,
                'staff_url'     => $user->url,
                'staff_name'    => $user->name,
                'submission_id' => $submission->id,
            ]);

            /*
                if (!$this->logAdminAction($user, 'Submission Approved', 'Approved submission <a href="'.$submission->viewurl.'">#'.$submission->id.'</a>')) {
                    throw new \Exception('Failed to log admin action.');
                }
            */

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a submission.
     *
     * @param mixed $data the data of the submission to be deleted
     * @param mixed $user the user performing the deletion
     */
    public function deleteSubmission($submission, $data, $user)
    {
        DB::beginTransaction();
        try {
            // 1. check that the submission exists
            // 2. check that the submission is a draft
            if (! isset($data['submission'])) {
                $submission = QueueSubmission::where('status', 'Draft')->where('id', $data['submission_id'])->first();
            } elseif ($data['submission']->status == 'Pending') {
                $submission = $data['submission'];
            } else {
                $submission = null;
            }
            if (! $submission) {
                throw new \Exception('Invalid submission.');
            }
            if ($user->id != $submission->user_id) {
                throw new \Exception('This is not your submission.');
            }

            $queue = $submission->queue;

            //carry out custom deletions
            $service = $submission->queue->service;

            if (method_exists($queue->service, 'delete')) {
                if (! $service->delete($queue, $data, $user, $submission)) {
                    foreach ($service->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }
                    throw new \Exception("Failed to handle submission.");
                }
            }

            if ($submission->queue->configSet('character_submit')) {
                // Remove characters and attachments.
                QueueSubmissionCharacter::where('queue_submission_id', $submission->id)->delete();
            }

            if ($submission->queue->configSet('item_consume')) {
                $this->removeAttachments($submission);
            }
            $submission->delete();

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**************************************************************************************************************
     *
     * ATTACHMENT FUNCTIONS
     *
     **************************************************************************************************************/

    /**
     * Creates user attachments for a submission.
     *
     * @param mixed $submission the submission object
     * @param mixed $data       the data for creating the attachments
     * @param mixed $user       the user object
     */
    private function createUserAttachments($submission, $data, $user)
    {
        $userAssets = createAssetsArray();

        // Attach items. Technically, the user doesn't lose ownership of the item - we're just adding an additional holding field.
        // We're also not going to add logs as this might add unnecessary fluff to the logs and the items still belong to the user.
        if (isset($data['stack_id'])) {
            foreach ($data['stack_id'] as $stackId) {
                $stack = UserItem::with('item')->find($stackId);
                if (! $stack || $stack->user_id != $user->id) {
                    throw new \Exception('Invalid item selected.');
                }
                if (! isset($data['stack_quantity'][$stackId])) {
                    throw new \Exception('Invalid quantity selected.');
                }
                $stack->submission_count += $data['stack_quantity'][$stackId];
                $stack->save();

                addAsset($userAssets, $stack, $data['stack_quantity'][$stackId]);
            }
        }

        // Attach currencies.
        if (isset($data['currency_id'])) {
            foreach ($data['currency_id'] as $holderKey => $currencyIds) {
                $holder     = explode('-', $holderKey);
                $holderType = $holder[0];
                $holderId   = $holder[1];

                $holder = User::find($holderId);

                $currencyManager = new CurrencyManager;
                foreach ($currencyIds as $key => $currencyId) {
                    $currency = Currency::find($currencyId);
                    if (! $currency) {
                        throw new \Exception('Invalid currency selected.');
                    }
                    if ($data['currency_quantity'][$holderKey][$key] < 0) {
                        throw new \Exception('Cannot attach a negative amount of currency.');
                    }
                    if (! $currencyManager->debitCurrency($holder, null, null, null, $currency, $data['currency_quantity'][$holderKey][$key])) {
                        throw new \Exception('Invalid currency/quantity selected.');
                    }

                    addAsset($userAssets, $currency, $data['currency_quantity'][$holderKey][$key]);
                }
            }
        }

        // Get a list of rewards, then create the submission itself
        $queueRewards = createAssetsArray();
        if ($submission->status == 'Pending' && isset($submission->queue_id) && $submission->queue_id) {
            if ($submission->queue->rewards) {
                foreach ($submission->queue->rewardItems as $key => $type) {
                    foreach ($type as $asset) {
                        addAsset($queueRewards, $asset['asset'], $asset['quantity']);
                    }
                }
            }
        }
        $queueRewards = mergeAssetsArrays($queueRewards, $this->processRewards($data, false));

        return [
            'userAssets'   => $userAssets,
            'queueRewards' => $queueRewards,
        ];
    }

    /**
     * Creates character attachments for a submission.
     *
     * @param mixed $submission the submission object
     * @param mixed $data       the data for creating character attachments
     */
    private function createCharacterAttachments($submission, $data, $service, $user)
    {
        DB::beginTransaction();

        try {
            // The character identification comes in both the slug field and as character IDs
            // that key the reward ID/quantity arrays.
            // We'll need to match characters to the rewards for them.
            // First, check if the characters are accessible to begin with.
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->visible()->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters do not exist.');
                }
            } else {
                $characters = [];
            }
            //process any relevant data
            if (method_exists($service, 'processCharacters')) {

                if (! $chardata = $service->processCharacters($submission->queue, $data, $submission)) {
                    foreach ($service->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }
                    throw new \Exception("Failed to handle submission characters.");
                }
            }
            // Attach characters
            foreach ($characters as $c) {
                //and for a specific character
                if (method_exists($service, 'processCharacterAttachments')) {
                    if (! $assets = $service->processCharacterAttachments($submission->queue, $data + ['character_id' => $c->id], $submission)) {
                        foreach ($service->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }
                        throw new \Exception("Failed to handle submission characters.");
                    }
                }
                // Now we have a clean set of assets (redundant data is gone, duplicate entries are merged)
                // so we can attach the character to the submission
                QueueSubmissionCharacter::create([
                    'character_id'        => $c->id,
                    'queue_submission_id' => $submission->id,
                    'data'                => method_exists($service, 'finalizeCharacterAttachments') ? $service->finalizeCharacterAttachments($submission->queue, $data + ['character_id' => $c->id], $submission, $user) : null,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Removes attachments from a submission.
     *
     * @param mixed $submission the submission object
     */
    private function removeAttachments($submission)
    {
        // This occurs when a draft is edited or rejected.

        // Return all added items
        $addonData = $submission->data['user'];
        if (isset($addonData['user_items'])) {
            foreach ($addonData['user_items'] as $userItemId => $quantity) {
                $userItemRow = UserItem::find($userItemId);
                if (! $userItemRow) {
                    throw new \Exception('Cannot return an invalid item. (' . $userItemId . ')');
                }
                if ($userItemRow->submission_count < $quantity) {
                    throw new \Exception('Cannot return more items than was held. (' . $userItemId . ')');
                }
                $userItemRow->submission_count -= $quantity;
                $userItemRow->save();
            }
        }

        // And currencies
        $currencyManager = new CurrencyManager;
        if (isset($addonData['currencies']) && $addonData['currencies']) {
            foreach ($addonData['currencies'] as $currencyId => $quantity) {
                $currency = Currency::find($currencyId);
                if (! $currency) {
                    throw new \Exception('Cannot return an invalid currency. (' . $currencyId . ')');
                }
                if (! $currencyManager->creditCurrency(null, $submission->user, null, null, $currency, $quantity)) {
                    throw new \Exception('Could not return currency to user. (' . $currencyId . ')');
                }
            }
        }
    }

    /**
     * Processes reward data into a format that can be used for distribution.
     *
     * @param array $data
     * @param bool  $isCharacter
     * @param bool  $isStaff
     *
     * @return array
     */
    private function processRewards($data, $isCharacter, $isStaff = false)
    {
        if ($isCharacter) {
            $assets = createAssetsArray(true);

            if (isset($data['character_currency_id'][$data['character_id']]) && isset($data['character_quantity'][$data['character_id']])) {
                foreach ($data['character_currency_id'][$data['character_id']] as $key => $currency) {
                    if ($data['character_quantity'][$data['character_id']][$key]) {
                        addAsset($assets, $data['currencies'][$currency], $data['character_quantity'][$data['character_id']][$key]);
                    }
                }
            } elseif (isset($data['character_rewardable_type'][$data['character_id']]) && isset($data['character_rewardable_id'][$data['character_id']]) && isset($data['character_rewardable_quantity'][$data['character_id']])) {
                $data['character_rewardable_id'] = array_map([$this, 'innerNull'], $data['character_rewardable_id']);

                foreach ($data['character_rewardable_id'][$data['character_id']] as $key => $reward) {
                    switch ($data['character_rewardable_type'][$data['character_id']][$key]) {
                        case 'Currency':if ($data['character_rewardable_quantity'][$data['character_id']][$key]) {
                                addAsset($assets, $data['currencies'][$reward], $data['character_rewardable_quantity'][$data['character_id']][$key]);
                            }break;
                        case 'Item':if ($data['character_rewardable_quantity'][$data['character_id']][$key]) {
                                addAsset($assets, $data['items'][$reward], $data['character_rewardable_quantity'][$data['character_id']][$key]);
                            }break;
                        case 'LootTable':if ($data['character_rewardable_quantity'][$data['character_id']][$key]) {
                                addAsset($assets, $data['tables'][$reward], $data['character_rewardable_quantity'][$data['character_id']][$key]);
                            }break;
                    }
                }
            }

            return $assets;
        } else {
            $assets = createAssetsArray(false);
            // Process the additional rewards
            if (isset($data['rewardable_type']) && $data['rewardable_type']) {
                foreach ($data['rewardable_type'] as $key => $type) {
                    $reward = null;
                    switch ($type) {
                        case 'Item':
                            $reward = Item::find($data['rewardable_id'][$key]);
                            break;
                        case 'Currency':
                            $reward = Currency::find($data['rewardable_id'][$key]);
                            if (! $reward->is_user_owned) {
                                throw new \Exception('Invalid currency selected.');
                            }
                            break;
                        case 'LootTable':
                            if (! $isStaff) {
                                break;
                            }
                            $reward = LootTable::find($data['rewardable_id'][$key]);
                            break;
                        case 'Raffle':
                            if (! $isStaff) {
                                break;
                            }
                            $reward = Raffle::find($data['rewardable_id'][$key]);
                            break;
                    }
                    if (! $reward) {
                        continue;
                    }
                    addAsset($assets, $reward, $data['quantity'][$key]);
                }
            }

            return $assets;
        }
    }

    /**
     * Removes the attachments associated with a queue from a submission.
     *
     * @param mixed $submission the submission object
     */
    private function removeQueueAttachments($submission)
    {
        $assets = $submission->data;
        // Get a list of rewards, then create the submission itself
        $queueRewards = createAssetsArray();
        $queueRewards = mergeAssetsArrays($queueRewards, parseAssetData($assets['rewards']));
        if (isset($submission->queue_id) && $submission->queue_id) {
            if ($submission->queue->rewards) {
                foreach ($submission->queue->rewardItems as $key => $type) {
                    foreach ($type as $asset) {
                        removeAsset($queueRewards, $asset['asset'], $asset['quantity']);
                    }
                }
            }
        }

        return $queueRewards;
    }
}
