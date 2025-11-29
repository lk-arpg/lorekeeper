<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Prompt\Prompt;
use App\Models\Raffle\Raffle;
use App\Models\Submission\Submission;
use App\Models\Submission\SubmissionCharacter;
use App\Models\User\User;
use App\Models\User\UserItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SubmissionManager extends Service {
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
     * @param bool  $isClaim
     * @param mixed $isDraft
     *
     * @return mixed
     */
    public function createSubmission($data, $user, $isClaim = false, $isDraft = false) {
        DB::beginTransaction();

        try {
            // 1. check that the prompt can be submitted at this time
            // 2. check that the characters selected exist (are visible too)
            // 3. check that the currencies selected can be attached to characters
            if (!$isClaim && !Settings::get('is_prompts_open')) {
                throw new \Exception('The prompt queue is closed for submissions.');
            } elseif ($isClaim && !Settings::get('is_claims_open')) {
                throw new \Exception('The claim queue is closed for submissions.');
            }
            if (!$isClaim && !isset($data['prompt_id'])) {
                throw new \Exception('Please select a prompt.');
            }
            if (!$isClaim) {
                $prompt = Prompt::active()->where('id', $data['prompt_id'])->with('rewards')->first();
                if (!$prompt) {
                    throw new \Exception('Invalid prompt selected.');
                }

                if ($prompt->staff_only && !$user->isStaff) {
                    throw new \Exception('This prompt may only be submitted to by staff members.');
                }
            } else {
                $prompt = null;
            }

            // Create the submission itself.
            $submission = Submission::create([
                'user_id'   => $user->id,
                'url'       => $data['url'] ?? null,
                'status'    => $isDraft ? 'Draft' : 'Pending',
                'comments'  => $data['comments'],
                'data'      => null,
            ] + ($isClaim ? [] : [
                'prompt_id' => $prompt->id,
            ]));

            // Set items that have been attached.
            $assets = $this->createUserAttachments($submission, $data, $user);
            $userAssets = $assets['userAssets'];
            $promptRewards = $assets['promptRewards'];
            $characterRewards = $assets['characterRewards'];

            $submission->update([
                'data' => [
                    'user'              => Arr::only(getDataReadyAssets($userAssets), ['user_items', 'currencies']),
                    'rewards'           => getDataReadyAssets($promptRewards),
                    'character_rewards' => getDataReadyAssets($characterRewards),
                ] + (config('lorekeeper.settings.allow_gallery_submissions_on_prompts') ? ['gallery_submission_id' => $data['gallery_submission_id'] ?? null] : []),
            ]);

            // Set characters that have been attached.
            $this->createCharacterAttachments($submission, $data, $characterRewards);

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
     * @param bool  $isClaim
     * @param mixed $submission
     * @param mixed $isSubmit
     *
     * @return mixed
     */
    public function editSubmission($submission, $data, $user, $isClaim = false, $isSubmit = false) {
        DB::beginTransaction();

        try {
            // 1. check that the prompt can be submitted at this time
            // 2. check that the characters selected exist (are visible too)
            // 3. check that the currencies selected can be attached to characters
            if (!$isClaim && !Settings::get('is_prompts_open')) {
                throw new \Exception('The prompt queue is closed for submissions.');
            } elseif ($isClaim && !Settings::get('is_claims_open')) {
                throw new \Exception('The claim queue is closed for submissions.');
            }
            if (!$isClaim && !isset($data['prompt_id'])) {
                throw new \Exception('Please select a prompt.');
            }
            if (!$isClaim) {
                $prompt = Prompt::active()->where('id', $data['prompt_id'])->with('rewards')->first();
                if (!$prompt) {
                    throw new \Exception('Invalid prompt selected.');
                }
            } else {
                $prompt = null;
            }

            // First, return all items and currency applied.
            // Also, as this is an edit, delete all attached characters to be re-applied later.
            $this->removeAttachments($submission);
            SubmissionCharacter::where('submission_id', $submission->id)->delete();

            if ($isSubmit) {
                $submission->update(['status' => 'Pending']);
            }

            // Then, re-attach everything fresh.
            $assets = $this->createUserAttachments($submission, $data, $user);
            $userAssets = $assets['userAssets'];
            $promptRewards = $assets['promptRewards'];
            $characterRewards = $assets['characterRewards'];
            $this->createCharacterAttachments($submission, $data, $characterRewards);

            // Modify submission
            $submission->update([
                'url'           => $data['url'] ?? null,
                'updated_at'    => Carbon::now(),
                'comments'      => $data['comments'],
                'data'          => [
                    'user'              => Arr::only(getDataReadyAssets($userAssets), ['user_items', 'currencies']),
                    'rewards'           => getDataReadyAssets($promptRewards),
                    'character_rewards' => getDataReadyAssets($characterRewards),
                ] + (config('lorekeeper.settings.allow_gallery_submissions_on_prompts') ? ['gallery_submission_id' => $data['gallery_submission_id'] ?? null] : []),
            ] + ($isClaim ? [] : ['prompt_id' => $prompt->id]));

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
    public function cancelSubmission($data, $user) {
        DB::beginTransaction();

        try {
            // 1. check that the submission exists
            // 2. check that the submission is pending
            if (!isset($data['submission'])) {
                $submission = Submission::where('status', 'Pending')->where('id', $data['id'])->first();
            } elseif ($data['submission']->status == 'Pending') {
                $submission = $data['submission'];
            } else {
                $submission = null;
            }
            if (!$submission) {
                throw new \Exception('Invalid submission.');
            }

            // Set staff comments
            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

            $assets = $submission->data;
            $userAssets = $assets['user'];
            // Remove prompt-only rewards
            $promptRewards = $this->removePromptAttachments($submission);

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
                        'user'                  => $userAssets,
                        'rewards'               => getDataReadyAssets($promptRewards),
                        'gallery_submission_id' => $submission->data['gallery_submission_id'] ?? null,
                    ], // list of rewards and addons
                ]);

                Notifications::create($submission->prompt_id ? 'SUBMISSION_CANCELLED' : 'CLAIM_CANCELLED', $submission->user, [
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
                        'user'                  => $userAssets,
                        'rewards'               => getDataReadyAssets($promptRewards),
                        'gallery_submission_id' => $submission->data['gallery_submission_id'] ?? null,
                    ], // list of rewards and addons
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
    public function rejectSubmission($data, $user) {
        DB::beginTransaction();

        try {
            // 1. check that the submission exists
            // 2. check that the submission is pending
            if (!isset($data['submission'])) {
                $submission = Submission::where('status', 'Pending')->where('id', $data['id'])->first();
            } elseif ($data['submission']->status == 'Pending') {
                $submission = $data['submission'];
            } else {
                $submission = null;
            }
            if (!$submission) {
                throw new \Exception('Invalid submission.');
            }

            // Return all items and currency applied.
            $this->removeAttachments($submission);

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

            Notifications::create($submission->prompt_id ? 'SUBMISSION_REJECTED' : 'CLAIM_REJECTED', $submission->user, [
                'staff_url'     => $user->url,
                'staff_name'    => $user->name,
                'submission_id' => $submission->id,
            ]);

            if (!$this->logAdminAction($user, 'Submission Rejected', 'Rejected submission <a href="'.$submission->viewurl.'">#'.$submission->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

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
    public function approveSubmission($data, $user) {
        DB::beginTransaction();

        try {
            // 1. check that the submission exists
            // 2. check that the submission is pending
            $submission = Submission::where('status', 'Pending')->where('id', $data['id'])->first();
            if (!$submission) {
                throw new \Exception('Invalid submission.');
            }

            // Remove any added items, hold counts, and add logs
            $addonData = $submission->data['user'];
            $inventoryManager = new InventoryManager;
            if (isset($addonData['user_items'])) {
                $stacks = $addonData['user_items'];
                foreach ($addonData['user_items'] as $userItemId => $quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                    }
                    if ($userItemRow->submission_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                    }
                    $userItemRow->submission_count -= $quantity;
                    $userItemRow->save();
                }

                // Workaround for user not being unset after inventory shuffling, preventing proper staff ID assignment
                $staff = $user;

                foreach ($stacks as $stackId=> $quantity) {
                    $stack = UserItem::find($stackId);
                    $user = User::find($submission->user_id);
                    if (!$inventoryManager->debitStack($user, $submission->prompt_id ? 'Prompt Approved' : 'Claim Approved', ['data' => 'Item used in submission (<a href="'.$submission->viewUrl.'">#'.$submission->id.'</a>)'], $stack, $quantity)) {
                        throw new \Exception('Failed to create log for item stack.');
                    }
                }

                // Set user back to the processing staff member, now that addons have been properly processed.
                $user = $staff;
            }

            // Log currency removal, etc.
            $currencyManager = new CurrencyManager;
            if (isset($addonData['currencies']) && $addonData['currencies']) {
                foreach ($addonData['currencies'] as $currencyId=> $quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currencyManager->createLog(
                        $submission->user_id,
                        'User',
                        null,
                        null,
                        $submission->prompt_id ? 'Prompt Approved' : 'Claim Approved',
                        'Used in '.($submission->prompt_id ? 'prompt' : 'claim').' (<a href="'.$submission->viewUrl.'">#'.$submission->id.'</a>)',
                        $currencyId,
                        $quantity
                    )) {
                        throw new \Exception('Failed to create currency log.');
                    }
                }
            }

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

            // Get the updated set of rewards
            $rewards = $this->processRewards($data, false, true);

            // Logging data
            $promptLogType = $submission->prompt_id ? 'Prompt Rewards' : 'Claim Rewards';
            $promptData = [
                'data' => 'Received rewards for '.($submission->prompt_id ? 'submission' : 'claim').' (<a href="'.$submission->viewUrl.'">#'.$submission->id.'</a>)',
            ];

            // Distribute user rewards
            if (!$rewards = fillUserAssets($rewards, $user, $submission->user, $promptLogType, $promptData)) {
                throw new \Exception('Failed to distribute rewards to user.');
            }

            // Retrieve all reward IDs for characters
            $currencyIds = [];
            $itemIds = [];
            $tableIds = [];
            if (isset($data['character_currency_id'])) {
                foreach ($data['character_currency_id'] as $c) {
                    foreach ($c as $currencyId) {
                        $currencyIds[] = $currencyId;
                    }
                } // Non-expanded character rewards
            } elseif (isset($data['character_rewardable_id'])) {
                $data['character_rewardable_id'] = array_map([$this, 'innerNull'], $data['character_rewardable_id']);
                foreach ($data['character_rewardable_id'] as $ckey => $c) {
                    foreach ($c as $key                            => $id) {
                        switch ($data['character_rewardable_type'][$ckey][$key]) {
                            case 'Currency': $currencyIds[] = $id;
                                break;
                            case 'Item': $itemIds[] = $id;
                                break;
                            case 'LootTable': $tableIds[] = $id;
                                break;
                        }
                    }
                } // Expanded character rewards
            }
            array_unique($currencyIds);
            array_unique($itemIds);
            array_unique($tableIds);
            $currencies = Currency::whereIn('id', $currencyIds)->where('is_character_owned', 1)->get()->keyBy('id');
            $items = Item::whereIn('id', $itemIds)->get()->keyBy('id');
            $tables = LootTable::whereIn('id', $tableIds)->get()->keyBy('id');

            // We're going to remove all characters from the submission and reattach them with the updated data
            $submission->characters()->delete();

            // Distribute character rewards
            foreach ($characters as $c) {
                // Users might not pass in clean arrays (may contain redundant data) so we need to clean that up
                $assets = $this->processRewards($data + ['character_id' => $c->id, 'currencies' => $currencies, 'items' => $items, 'tables' => $tables], true);

                if (!$assets = fillCharacterAssets($assets, $user, $c, $promptLogType, $promptData, $submission->user)) {
                    throw new \Exception('Failed to distribute rewards to character.');
                }

                SubmissionCharacter::create([
                    'character_id'  => $c->id,
                    'submission_id' => $submission->id,
                    'data'          => getDataReadyAssets($assets),
                ]);
            }

            // Increment user submission count if it's a prompt
            if ($submission->prompt_id) {
                $user->settings->submission_count++;
                $user->settings->save();
            }

            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            } else {
                $data['parsed_staff_comments'] = null;
            }

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
                    'user'                  => $addonData,
                    'rewards'               => getDataReadyAssets($rewards),
                    'gallery_submission_id' => $submission->data['gallery_submission_id'] ?? null,
                ], // list of rewards
            ]);

            Notifications::create($submission->prompt_id ? 'SUBMISSION_APPROVED' : 'CLAIM_APPROVED', $submission->user, [
                'staff_url'     => $user->url,
                'staff_name'    => $user->name,
                'submission_id' => $submission->id,
            ]);

            if (!$this->logAdminAction($user, 'Submission Approved', 'Approved submission <a href="'.$submission->viewurl.'">#'.$submission->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

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
    public function deleteSubmission($data, $user) {
        DB::beginTransaction();
        try {
            // 1. check that the submission exists
            // 2. check that the submission is a draft
            if (!isset($data['submission'])) {
                $submission = Submission::where('status', 'Draft')->where('id', $data['id'])->first();
            } elseif ($data['submission']->status == 'Pending') {
                $submission = $data['submission'];
            } else {
                $submission = null;
            }
            if (!$submission) {
                throw new \Exception('Invalid submission.');
            }
            if ($user->id != $submission->user_id) {
                throw new \Exception('This is not your submission.');
            }

            // Remove characters and attachments.
            SubmissionCharacter::where('submission_id', $submission->id)->delete();
            $this->removeAttachments($submission);
            $submission->delete();

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**************************************************************************************************************
     *
     * PRIVATE FUNCTIONS
     *
     **************************************************************************************************************/

    /**
     * Helper function to remove all empty/zero/falsey values.
     *
     * @param array $value
     *
     * @return array
     */
    private function innerNull($value) {
        return array_values(array_filter($value));
    }

    /**
     * Processes reward data into a format that can be used for distribution.
     *
     * @param array $data
     * @param bool  $isCharacter
     * @param bool  $isStaff
     * @param bool  $isClaim
     *
     * @return array
     */
    private function processRewards($data, $isCharacter, $isStaff = false, $isClaim = false) {
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
                        case 'Currency': if ($data['character_rewardable_quantity'][$data['character_id']][$key]) {
                            addAsset($assets, $data['currencies'][$reward], $data['character_rewardable_quantity'][$data['character_id']][$key]);
                        } break;
                        case 'Item': if ($data['character_rewardable_quantity'][$data['character_id']][$key]) {
                            addAsset($assets, $data['items'][$reward], $data['character_rewardable_quantity'][$data['character_id']][$key]);
                        } break;
                        case 'LootTable': if ($data['character_rewardable_quantity'][$data['character_id']][$key]) {
                            addAsset($assets, $data['tables'][$reward], $data['character_rewardable_quantity'][$data['character_id']][$key]);
                        } break;
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
                            if (!$reward->is_user_owned) {
                                throw new \Exception('Invalid currency selected.');
                            }
                            break;
                        case 'LootTable':
                            if (!$isStaff) {
                                break;
                            }
                            $reward = LootTable::find($data['rewardable_id'][$key]);
                            break;
                        case 'Raffle':
                            if (!$isStaff && !$isClaim) {
                                break;
                            }
                            $reward = Raffle::find($data['rewardable_id'][$key]);
                            break;
                    }
                    if (!$reward) {
                        continue;
                    }
                    addAsset($assets, $reward, $data['quantity'][$key]);
                }
            }

            return $assets;
        }
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
    private function createUserAttachments($submission, $data, $user) {
        $userAssets = createAssetsArray();

        // Attach items. Technically, the user doesn't lose ownership of the item - we're just adding an additional holding field.
        // We're also not going to add logs as this might add unnecessary fluff to the logs and the items still belong to the user.
        if (isset($data['stack_id'])) {
            foreach ($data['stack_id'] as $stackId) {
                $stack = UserItem::with('item')->find($stackId);
                if (!$stack || $stack->user_id != $user->id) {
                    throw new \Exception('Invalid item selected.');
                }
                if (!isset($data['stack_quantity'][$stackId])) {
                    throw new \Exception('Invalid quantity selected.');
                }
                $stack->submission_count += $data['stack_quantity'][$stackId];
                $stack->save();

                addAsset($userAssets, $stack, $data['stack_quantity'][$stackId]);
            }
        }

        // Attach currencies.
        if (isset($data['currency_id'])) {
            foreach ($data['currency_id'] as $holderKey=>$currencyIds) {
                $holder = explode('-', $holderKey);
                $holderType = $holder[0];
                $holderId = $holder[1];

                $holder = User::find($holderId);

                $currencyManager = new CurrencyManager;
                foreach ($currencyIds as $key=>$currencyId) {
                    $currency = Currency::find($currencyId);
                    if (!$currency) {
                        throw new \Exception('Invalid currency selected.');
                    }
                    if ($data['currency_quantity'][$holderKey][$key] < 0) {
                        throw new \Exception('Cannot attach a negative amount of currency.');
                    }
                    if (!$currencyManager->debitCurrency($holder, null, null, null, $currency, $data['currency_quantity'][$holderKey][$key])) {
                        throw new \Exception('Invalid currency/quantity selected.');
                    }

                    addAsset($userAssets, $currency, $data['currency_quantity'][$holderKey][$key]);
                }
            }
        }

        // Get a list of rewards, then create the submission itself
        $promptRewards = createAssetsArray();
        $characterRewards = createAssetsArray();
        if ($submission->status == 'Pending' && isset($submission->prompt_id) && $submission->prompt_id) {
            foreach ($submission->prompt->rewards as $reward) {
                if ($reward->rewardable_recipient == 'User') {
                    addAsset($promptRewards, $reward->reward, $reward->quantity);
                } elseif ($reward->rewardable_recipient == 'Character') {
                    addAsset($characterRewards, $reward->reward, $reward->quantity);
                }
            }
        }
        $promptRewards = mergeAssetsArrays($promptRewards, $this->processRewards($data, false));

        return [
            'userAssets'       => $userAssets,
            'promptRewards'    => $promptRewards,
            'characterRewards' => $characterRewards,
        ];
    }

    /**
     * Removes the attachments associated with a prompt from a submission.
     *
     * @param mixed $submission the submission object
     */
    private function removePromptAttachments($submission) {
        $assets = $submission->data;
        // Get a list of rewards, then create the submission itself
        $promptRewards = createAssetsArray();
        $promptRewards = mergeAssetsArrays($promptRewards, parseAssetData($assets['rewards']));
        if (isset($submission->prompt_id) && $submission->prompt_id) {
            foreach ($submission->prompt->rewards as $reward) {
                if ($reward->rewardable_recipient != 'User') {
                    continue;
                }
                removeAsset($promptRewards, $reward->reward, $reward->quantity);
            }

            // Remove character default rewards
            foreach ($submission->characters as $c) {
                $cRewards = parseAssetData($c->data);
                foreach ($submission->prompt->rewards as $reward) {
                    if ($reward->rewardable_recipient != 'Character') {
                        continue;
                    }
                    removeAsset($cRewards, $reward->reward, $reward->quantity);
                }
                $c->update(['data' => getDataReadyAssets($cRewards)]);
            }
        }

        return $promptRewards;
    }

    /**
     * Creates character attachments for a submission.
     *
     * @param mixed      $submission     the submission object
     * @param mixed      $data           the data for creating character attachments
     * @param mixed|null $defaultRewards
     */
    private function createCharacterAttachments($submission, $data, $defaultRewards = null) {
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

        // Retrieve all reward IDs for characters
        $currencyIds = [];
        $itemIds = [];
        $tableIds = [];
        if (isset($data['character_currency_id'])) {
            foreach ($data['character_currency_id'] as $c) {
                foreach ($c as $currencyId) {
                    $currencyIds[] = $currencyId;
                }
            } // Non-expanded character rewards
        } elseif (isset($data['character_rewardable_id'])) {
            $data['character_rewardable_id'] = array_map([$this, 'innerNull'], $data['character_rewardable_id']);
            foreach ($data['character_rewardable_id'] as $ckey => $c) {
                foreach ($c as $key => $id) {
                    switch ($data['character_rewardable_type'][$ckey][$key]) {
                        case 'Currency': $currencyIds[] = $id;
                            break;
                        case 'Item': $itemIds[] = $id;
                            break;
                        case 'LootTable': $tableIds[] = $id;
                            break;
                    }
                }
            } // Expanded character rewards
        }
        array_unique($currencyIds);
        array_unique($itemIds);
        array_unique($tableIds);
        $currencies = Currency::whereIn('id', $currencyIds)->where('is_character_owned', 1)->get()->keyBy('id');
        $items = Item::whereIn('id', $itemIds)->get()->keyBy('id');
        $tables = LootTable::whereIn('id', $tableIds)->get()->keyBy('id');

        // Attach characters
        foreach ($characters as $c) {
            // Users might not pass in clean arrays (may contain redundant data) so we need to clean that up
            $assets = $this->processRewards($data + ['character_id' => $c->id, 'currencies' => $currencies, 'items' => $items, 'tables' => $tables], true);

            if ($defaultRewards) {
                $assets = mergeAssetsArrays($assets, $defaultRewards);
            }

            // Now we have a clean set of assets (redundant data is gone, duplicate entries are merged)
            // so we can attach the character to the submission
            SubmissionCharacter::create([
                'character_id'  => $c->id,
                'submission_id' => $submission->id,
                'data'          => getDataReadyAssets($assets),
            ]);
        }

        return true;
    }

    /**
     * Removes attachments from a submission.
     *
     * @param mixed $submission the submission object
     */
    private function removeAttachments($submission) {
        // This occurs when a draft is edited or rejected.

        // Return all added items
        $addonData = $submission->data['user'];
        if (isset($addonData['user_items'])) {
            foreach ($addonData['user_items'] as $userItemId => $quantity) {
                $userItemRow = UserItem::find($userItemId);
                if (!$userItemRow) {
                    throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                }
                if ($userItemRow->submission_count < $quantity) {
                    throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                }
                $userItemRow->submission_count -= $quantity;
                $userItemRow->save();
            }
        }

        // And currencies
        $currencyManager = new CurrencyManager;
        if (isset($addonData['currencies']) && $addonData['currencies']) {
            foreach ($addonData['currencies'] as $currencyId=>$quantity) {
                $currency = Currency::find($currencyId);
                if (!$currency) {
                    throw new \Exception('Cannot return an invalid currency. ('.$currencyId.')');
                }
                if (!$currencyManager->creditCurrency(null, $submission->user, null, null, $currency, $quantity)) {
                    throw new \Exception('Could not return currency to user. ('.$currencyId.')');
                }
            }
        }
    }
}
