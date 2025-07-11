<?php

/*
|--------------------------------------------------------------------------
| Asset Helpers
|--------------------------------------------------------------------------
|
| These are used to manage asset arrays, which are used in keeping
| track of/distributing rewards.
|
*/

/**
 * Calculates amount of group currency a submission should be awarded
 * based on form input. Corresponds to the GroupCurrencyForm configured in
 * app/Forms.
 *
 * @param array $data
 *
 * @return int
 */
function calculateGroupCurrency($data) {
    // Sets a starting point for the total so that numbers can be added to it.
    // Don't change this!
    $total = 0;

    // You'll need the names of the form fields you specified both in the form config and above.
    // You can get a particular field's value with $data['form_name'], for instance, $data['art_finish']

    // This differentiates how values are calculated depending on the type of content being submitted.
    $pieceType = collect($data['piece_type'])->flip();

    // For instance, if the user selected that the submission has a visual art component,
    // these actions will be performed:
    if ($pieceType->has('art')) {
        // This adds values to the total!
        $total += ($data['art_finish'] + $data['art_type']);
        // This multiplies each option selected in the "bonus" form field by
        // the result from the "art type" field, and adds it to the total.
        if (isset($data['art_bonus'])) {
            foreach ((array) $data['art_bonus'] as $bonus) {
                $total += (round($bonus) * $data['art_type']);
            }
        }
    }

    // Likewise for if the user selected that the submission has a written component:
    if ($pieceType->has('lit')) {
        // This divides the word count by 100, rounds the result, and then multiplies it by one--
        // so, effectively, for every 100 words, 1 of the currency is awarded.
        // You can adjust these numbers as you see fit.
        $total += (round($data['word_count'] / 100) * 1);
    }

    // And if it has a crafted or other physical object component:
    if ($pieceType->has('craft')) {
        // This just adds 4! You can adjust this as you desire.
        $total += 4;
    }

    // Hands the resulting total off. Don't change this!
    return $total;
}

/**
 * Gets the asset keys for an array depending on whether the
 * assets being managed are owned by a user or character.
 *
 * @param bool $isCharacter
 *
 * @return array
 */
function getAssetKeys($isCharacter = false) {
    if (!$isCharacter) {
        return ['items', 'currencies', 'raffle_tickets', 'loot_tables', 'user_items', 'characters'];
    } else {
        return ['currencies', 'items', 'character_items', 'loot_tables'];
    }
}

/**
 * Gets the model name for an asset type.
 * The asset type has to correspond to one of the asset keys above.
 *
 * @param string $type
 * @param bool   $namespaced
 *
 * @return string
 */
function getAssetModelString($type, $namespaced = true) {
    switch ($type) {
        case 'items': case 'item':
            if ($namespaced) {
                return '\App\Models\Item\Item';
            } else {
                return 'Item';
            }
            break;

        case 'currencies': case 'currency':
            if ($namespaced) {
                return '\App\Models\Currency\Currency';
            } else {
                return 'Currency';
            }
            break;

        case 'raffle_tickets':
            if ($namespaced) {
                return '\App\Models\Raffle\Raffle';
            } else {
                return 'Raffle';
            }
            break;

        case 'loot_tables':
            if ($namespaced) {
                return '\App\Models\Loot\LootTable';
            } else {
                return 'LootTable';
            }
            break;

        case 'user_items':
            if ($namespaced) {
                return '\App\Models\User\UserItem';
            } else {
                return 'UserItem';
            }
            break;

        case 'characters':
            if ($namespaced) {
                return '\App\Models\Character\Character';
            } else {
                return 'Character';
            }
            break;

        case 'character_items':
            if ($namespaced) {
                return '\App\Models\Character\CharacterItem';
            } else {
                return 'CharacterItem';
            }
            break;
    }

    return null;
}

/**
 * Initialises a new blank assets array, keyed by the asset type.
 *
 * @param bool $isCharacter
 *
 * @return array
 */
function createAssetsArray($isCharacter = false) {
    $keys = getAssetKeys($isCharacter);
    $assets = [];
    foreach ($keys as $key) {
        $assets[$key] = [];
    }

    return $assets;
}

/**
 * Merges 2 asset arrays.
 *
 * @param array $first
 * @param array $second
 *
 * @return array
 */
function mergeAssetsArrays($first, $second) {
    $keys = getAssetKeys();
    foreach ($keys as $key) {
        foreach ($second[$key] as $item) {
            addAsset($first, $item['asset'], $item['quantity']);
        }
    }

    return $first;
}

/**
 * Adds an asset to the given array.
 * If the asset already exists, it adds to the quantity.
 *
 * @param array $array
 * @param mixed $asset
 * @param int   $quantity
 */
function addAsset(&$array, $asset, $quantity = 1) {
    if (!$asset) {
        return;
    }
    if (isset($array[$asset->assetType][$asset->id])) {
        $array[$asset->assetType][$asset->id]['quantity'] += $quantity;
    } else {
        $array[$asset->assetType][$asset->id] = ['asset' => $asset, 'quantity' => $quantity];
    }
}

/**
 * Removes an asset from the given array, if it exists.
 *
 * @param array $array
 * @param mixed $asset
 * @param int   $quantity
 */
function removeAsset(&$array, $asset, $quantity = 1) {
    if (!$asset) {
        return;
    }
    if (isset($array[$asset->assetType][$asset->id])) {
        $array[$asset->assetType][$asset->id]['quantity'] -= $quantity;
        if ($array[$asset->assetType][$asset->id]['quantity'] == 0) {
            unset($array[$asset->assetType][$asset->id]);
        }
    }
}

/**
 * Get a clean version of the asset array to store in the database,
 * where each asset is listed in [id => quantity] format.
 *
 * @param array $array
 * @param bool  $isCharacter
 *
 * @return array
 */
function getDataReadyAssets($array, $isCharacter = false) {
    $result = [];
    foreach ($array as $key => $type) {
        if ($type && !isset($result[$key])) {
            $result[$key] = [];
        }
        foreach ($type as $assetId => $assetData) {
            $result[$key][$assetId] = $assetData['quantity'];
        }
    }

    return $result;
}

/**
 * Retrieves the data associated with an asset array,
 * basically reversing the above function.
 *
 * @param array $array
 *
 * @return array
 */
function parseAssetData($array) {
    $assets = createAssetsArray();
    foreach ($array as $key => $contents) {
        $model = getAssetModelString($key);
        if ($model) {
            foreach ($contents as $id => $quantity) {
                $assets[$key][$id] = [
                    'asset'    => $model::find($id),
                    'quantity' => $quantity,
                ];
            }
        }
    }

    return $assets;
}

/**
 * Returns if two asset arrays are identical.
 *
 * @param array $first
 * @param array $second
 * @param mixed $isCharacter
 * @param mixed $absQuantities
 *
 * @return bool
 */
function compareAssetArrays($first, $second, $isCharacter = false, $absQuantities = false) {
    $keys = getAssetKeys($isCharacter);
    foreach ($keys as $key) {
        if (count($first[$key]) != count($second[$key])) {
            return false;
        }
        foreach ($first[$key] as $id => $asset) {
            if (!isset($second[$key][$id])) {
                return false;
            }

            if ($absQuantities) {
                if (abs($asset['quantity']) != abs($second[$key][$id]['quantity'])) {
                    return false;
                }
            } else {
                if ($asset['quantity'] != $second[$key][$id]['quantity']) {
                    return false;
                }
            }
        }
    }

    return true;
}

/**
 * Distributes the assets in an assets array to the given recipient (user).
 * Loot tables will be rolled before distribution.
 *
 * @param array                $assets
 * @param App\Models\User\User $sender
 * @param App\Models\User\User $recipient
 * @param string               $logType
 * @param string               $data
 * @param mixed|null           $selected
 *
 * @return array
 */
function fillUserAssets($assets, $sender, $recipient, $logType, $data, $selected = null) {
    // Roll on any loot tables
    if (isset($assets['loot_tables'])) {
        foreach ($assets['loot_tables'] as $table) {
            $assets = mergeAssetsArrays($assets, $table['asset']->roll($table['quantity']));
        }
        unset($assets['loot_tables']);
    }

    foreach ($assets as $key => $contents) {
        if ($key == 'items' && count($contents)) {
            $service = new App\Services\InventoryManager;
            foreach ($contents as $asset) {
                if ($asset['quantity'] < 0) {
                    if (!$selected) {
                        flash('No selected item found for debiting.')->error();

                        return false;
                    }

                    foreach ($selected as $stackData) {
                        if (!$service->debitStack($sender, $logType, $data, $stackData['stack'], $stackData['quantity'])) {
                            foreach ($service->errors()->getMessages()['error'] as $error) {
                                flash($error)->error();
                            }

                            return false;
                        }
                    }
                } else {
                    if (!$service->creditItem($sender, $recipient, $logType, $data, $asset['asset'], $asset['quantity'])) {
                        foreach ($service->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }

                        return false;
                    }
                }
            }
        } elseif ($key == 'currencies' && count($contents)) {
            $service = new App\Services\CurrencyManager;
            foreach ($contents as $asset) {
                if ($asset['quantity'] < 0) {
                    if (!$service->debitCurrency($sender, $recipient, $logType, $data['data'], $asset['asset'], abs($asset['quantity']))) {
                        foreach ($service->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }

                        return false;
                    }
                } else {
                    if (!$service->creditCurrency($sender, $recipient, $logType, $data['data'], $asset['asset'], $asset['quantity'])) {
                        foreach ($service->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }

                        return false;
                    }
                }
            }
        } elseif ($key == 'raffle_tickets' && count($contents)) {
            $service = new App\Services\RaffleManager;
            foreach ($contents as $asset) {
                if (!$service->addTicket($recipient, $asset['asset'], $asset['quantity'])) {
                    foreach ($service->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }

                    return false;
                }
            }
        } elseif ($key == 'user_items' && count($contents)) {
            $service = new App\Services\InventoryManager;
            foreach ($contents as $asset) {
                if (!$service->moveStack($sender, $recipient, $logType, $data, $asset['asset'], $asset['quantity'])) {
                    foreach ($service->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }

                    return false;
                }
            }
        } elseif ($key == 'characters' && count($contents)) {
            $service = new App\Services\CharacterManager;
            foreach ($contents as $asset) {
                if (!$service->moveCharacter($asset['asset'], $recipient, $data, $asset['quantity'], $logType)) {
                    foreach ($service->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }

                    return false;
                }
            }
        }
    }

    return $assets;
}

/**
 * Returns the total count of all assets in an asset array.
 *
 * @param array $array
 *
 * @return int
 */
function countAssets($array) {
    $count = 0;
    foreach ($array as $key => $contents) {
        foreach ($contents as $asset) {
            $count += $asset['quantity'];
        }
    }

    return $count;
}

/**
 * Distributes the assets in an assets array to the given recipient (character).
 * Loot tables will be rolled before distribution.
 *
 * @param array                          $assets
 * @param App\Models\User\User           $sender
 * @param App\Models\Character\Character $recipient
 * @param string                         $logType
 * @param string                         $data
 * @param mixed|null                     $submitter
 *
 * @return array
 */
function fillCharacterAssets($assets, $sender, $recipient, $logType, $data, $submitter = null) {
    if (!config('lorekeeper.extensions.character_reward_expansion.default_recipient') && $recipient->user) {
        $item_recipient = $recipient->user;
    } else {
        $item_recipient = $submitter;
    }

    // Roll on any loot tables
    if (isset($assets['loot_tables'])) {
        foreach ($assets['loot_tables'] as $table) {
            $assets = mergeAssetsArrays($assets, $table['asset']->roll($table['quantity']));
        }
        unset($assets['loot_tables']);
    }

    foreach ($assets as $key => $contents) {
        if ($key == 'currencies' && count($contents)) {
            $service = new App\Services\CurrencyManager;
            foreach ($contents as $asset) {
                if (!$service->creditCurrency($sender, ($asset['asset']->is_character_owned ? $recipient : $item_recipient), $logType, $data['data'], $asset['asset'], $asset['quantity'])) {
                    return false;
                }
            }
        } elseif ($key == 'items' && count($contents)) {
            $service = new App\Services\InventoryManager;
            foreach ($contents as $asset) {
                if (!$service->creditItem($sender, (($asset['asset']->category && $asset['asset']->category->is_character_owned) ? $recipient : $item_recipient), $logType, $data, $asset['asset'], $asset['quantity'])) {
                    return false;
                }
            }
        }
    }

    return $assets;
}

/**
 * Creates a rewards string from an asset array.
 *
 * @param array $array
 * @param mixed $useDisplayName
 * @param mixed $absQuantities
 *
 * @return string
 */
function createRewardsString($array, $useDisplayName = true, $absQuantities = false) {
    $string = [];
    foreach ($array as $key => $contents) {
        foreach ($contents as $asset) {
            if ($useDisplayName) {
                if ($key == 'currencies') {
                    $name = $asset['asset'] ? $asset['asset']->display(($absQuantities ? abs($asset['quantity']) : $asset['quantity'])) : 'Deleted '.ucfirst(str_replace('_', ' ', $key));
                    $string[] = $asset['asset'] ? $name : $name.' x'.($absQuantities ? abs($asset['quantity']) : $asset['quantity']);
                } else {
                    $name = $asset['asset']->displayName ?? ($asset['asset']->name ?? 'Deleted '.ucfirst(str_replace('_', ' ', $key)));
                    $string[] = $name.' x'.($absQuantities ? abs($asset['quantity']) : $asset['quantity']);
                }
            } else {
                $name = $asset['asset']->name ?? 'Deleted '.ucfirst(str_replace('_', ' ', $key));
                $string[] = $name.' x'.($absQuantities ? abs($asset['quantity']) : $asset['quantity']);
            }
        }
    }
    if (!count($string)) {
        return 'Nothing. :('; // :(
    }

    if (count($string) == 1) {
        return implode(', ', $string);
    }

    return implode(', ', array_slice($string, 0, count($string) - 1)).(count($string) > 2 ? ', and ' : ' and ').end($string);
}
