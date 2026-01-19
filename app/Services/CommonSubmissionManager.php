<?php

namespace App\Services;

class CommonSubmissionManager extends Service {
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
     * Creates character attachments for a submission.
     *
     * @param mixed      $submission     the submission object
     * @param mixed      $data           the data for creating character attachments
     * @param mixed|null $defaultRewards
     * @param mixed|null $service
     */
    private function createCharacterAttachments($submission, $data, $defaultRewards = null, $service = null) {
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

            if ($service) {
                // process any relevant data
                if (method_exists($service, 'processCharacters')) {
                    if (!$characterData = $service->processCharacters($submission->queue, $data, $submission)) {
                        foreach ($service->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }
                        throw new \Exception('Failed to handle submission characters.');
                    }
                }
            } else {
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
            }

            // Attach characters
            foreach ($characters as $c) {
                if ($service) {
                    // and for a specific character
                    if (method_exists($service, 'processCharacterAttachments')) {
                        if (!$assets = $service->processCharacterAttachments($submission->queue, $data + ['character_id' => $c->id], $submission)) {
                            foreach ($service->errors()->getMessages()['error'] as $error) {
                                flash($error)->error();
                            }
                            throw new \Exception('Failed to handle submission characters.');
                        }
                    }

                    // Now we have a clean set of assets (redundant data is gone, duplicate entries are merged)
                    // so we can attach the character to the submission
                    QueueSubmissionCharacter::create([
                        'character_id'        => $c->id,
                        'queue_submission_id' => $submission->id,
                        'data'                => method_exists($service, 'finalizeCharacterAttachments')
                                                    ? $service->finalizeCharacterAttachments($submission->queue, $data + ['character_id' => $c->id], $submission, Auth::user())
                                                    : null,
                    ]);
                } else {
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
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Removes the attachments associated with a submission.
     *
     * @param mixed $submission the submission object
     */
    private function removeSubmissionAttachments($submission) {
        $assets = $submission->data;
        // Get a list of rewards, then create the submission itself
        $rewards = createAssetsArray();
        $rewards = mergeAssetsArrays($rewards, parseAssetData($assets['rewards']));
        // TODO GENERICIZE THIS FUNCTION SOMEHOW
        if (isset($submission->prompt_id) && $submission->prompt_id) {
            foreach ($submission->prompt->rewards as $reward) {
                if ($reward->rewardable_recipient != 'User') {
                    continue;
                }
                removeAsset($rewards, $reward->reward, $reward->quantity);
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

        return $rewards;
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
