<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Character\CharacterTransfer;
use App\Models\Currency\Currency;
use App\Models\Trade;
use App\Models\User\User;
use App\Models\User\UserItem;
use Illuminate\Support\Facades\DB;

class TradeManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Trade Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of trade data.
    |
    */

    /**
     * Creates a new trade.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Trade
     */
    public function createTrade($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['recipient_id'])) {
                throw new \Exception('Please select a recipient.');
            }
            if ($data['recipient_id'] == $user->id) {
                throw new \Exception('Cannot start a trade with yourself.');
            }
            $recipient = User::find($data['recipient_id']);
            if ($recipient->is_banned) {
                throw new \Exception('The recipient is a banned user and cannot receive a trade.');
            }

            $trade = Trade::create([
                'sender_id'              => $user->id,
                'recipient_id'           => $data['recipient_id'],
                'status'                 => 'Open',
                'comments'               => $data['comments'] ?? null,
                'is_sender_confirmed'    => 0,
                'is_recipient_confirmed' => 0,
                'data'                   => null,
            ]);

            if ($assetData = $this->handleTradeAssets($trade, $data, $user)) {
                $trade->data = json_encode(['sender' => getDataReadyAssets($assetData['sender'])]);
                $trade->save();

                // send a notification
                Notifications::create('TRADE_RECEIVED', $recipient, [
                    'sender_url'  => $user->url,
                    'sender_name' => $user->name,
                    'trade_id'    => $trade->id,
                ]);

                return $this->commitReturn($trade);
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits a user's side of a trade.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Trade
     */
    public function editTrade($data, $user) {
        DB::beginTransaction();
        try {
            if (!isset($data['trade'])) {
                $trade = Trade::where('status', 'Open')->where('id', $data['id'])->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->first();
            } elseif ($data['trade']->status == 'Open') {
                $trade = $data['trade'];
            } else {
                $trade = null;
            }
            if (!$trade) {
                throw new \Exception('Invalid trade.');
            }

            if ($assetData = $this->handleTradeAssets($trade, $data, $user)) {
                $tradeData = $trade->data;
                $isSender = ($trade->sender_id == $user->id);
                $tradeData[$isSender ? 'sender' : 'recipient'] = getDataReadyAssets($assetData[$isSender ? 'sender' : 'recipient']);
                $trade->data = json_encode($tradeData);
                $trade->{'is_'.($isSender ? 'sender' : 'recipient').'_confirmed'} = 0;
                $trade->{'is_'.($isSender ? 'recipient' : 'sender').'_trade_confirmed'} = 0;
                $trade->save();

                return $this->commitReturn($trade);
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Cancels a trade.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Trade
     */
    public function cancelTrade($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['trade'])) {
                $trade = Trade::where('status', 'Open')->where('id', $data['id'])->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->first();
            } elseif ($data['trade']->status == 'Open') {
                $trade = $data['trade'];
            } else {
                $trade = null;
            }
            if (!$trade) {
                throw new \Exception('Invalid trade.');
            }

            if ($this->returnAttachments($trade)) {
                Notifications::create('TRADE_CANCELED', $trade->sender_id == $user->id ? $trade->recipient : $trade->sender, [
                    'sender_url'  => $user->url,
                    'sender_name' => $user->name,
                    'trade_id'    => $trade->id,
                ]);
                $trade->status = 'Canceled';
                $trade->save();

                return $this->commitReturn($trade);
            } else {
                throw new \Exception('Failed to cancel trade.');
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Confirms the user's offer.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Trade
     */
    public function confirmOffer($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['trade'])) {
                $trade = Trade::where('status', 'Open')->where('id', $data['id'])->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->first();
            } elseif ($data['trade']->status == 'Open') {
                $trade = $data['trade'];
            } else {
                $trade = null;
            }
            if (!$trade) {
                throw new \Exception('Invalid trade.');
            }

            // Mark the offer as confirmed
            if ($trade->sender_id == $user->id) {
                $trade->is_sender_confirmed = !$trade->is_sender_confirmed;

                // Reset trade confirmations if this is unconfirming an offer
                if (!$trade->is_sender_confirmed) {
                    $trade->is_sender_trade_confirmed = 0;
                    $trade->is_recipient_trade_confirmed = 0;
                } else {
                    Notifications::create('TRADE_UPDATE', $trade->recipient, [
                        'sender_url'  => $user->url,
                        'sender_name' => $user->name,
                        'trade_id'    => $trade->id,
                    ]);
                }
            } else {
                $trade->is_recipient_confirmed = !$trade->is_recipient_confirmed;

                // Reset trade confirmations if this is unconfirming an offer
                if (!$trade->is_recipient_confirmed) {
                    $trade->is_sender_trade_confirmed = 0;
                    $trade->is_recipient_trade_confirmed = 0;
                } else {
                    Notifications::create('TRADE_UPDATE', $trade->sender, [
                        'sender_url'  => $user->url,
                        'sender_name' => $user->name,
                        'trade_id'    => $trade->id,
                    ]);
                }
            }

            $trade->save();

            return $this->commitReturn($trade);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Confirms the trade for a user.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Trade
     */
    public function confirmTrade($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['trade'])) {
                $trade = Trade::where('status', 'Open')->where('id', $data['id'])->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->first();
            } elseif ($data['trade']->status == 'Open') {
                $trade = $data['trade'];
            } else {
                $trade = null;
            }
            if (!$trade) {
                throw new \Exception('Invalid trade.');
            }
            if (!$trade->isConfirmable) {
                throw new \Exception('Both parties are required to confirm their offers before the trade can be confirmed.');
            }

            if ($user->id == $trade->sender_id) {
                $trade->is_sender_trade_confirmed = 1;
            } else {
                $trade->is_recipient_trade_confirmed = 1;
            }

            if ($trade->is_sender_trade_confirmed && $trade->is_recipient_trade_confirmed) {
                if (!(Settings::get('open_transfers_queue') && (isset($trade->data['sender']['characters']) || isset($trade->data['recipient']['characters'])))) {
                    // Distribute the trade attachments
                    $this->creditAttachments($trade);

                    $trade->status = 'Completed';

                    // Notify both users
                    Notifications::create('TRADE_COMPLETED', $trade->sender, [
                        'trade_id' => $trade->id,
                    ]);
                    Notifications::create('TRADE_COMPLETED', $trade->recipient, [
                        'trade_id' => $trade->id,
                    ]);
                } else {
                    // Put the trade into the queue
                    $trade->status = 'Pending';

                    // Notify both users
                    Notifications::create('TRADE_CONFIRMED', $trade->sender, [
                        'trade_id' => $trade->id,
                    ]);
                    Notifications::create('TRADE_CONFIRMED', $trade->recipient, [
                        'trade_id' => $trade->id,
                    ]);
                }
            } else {
                // Notify the other user
                Notifications::create('TRADE_UPDATE', $user->id == $trade->sender_id ? $trade->recipient : $trade->sender, [
                    'sender_url'  => $user->url,
                    'sender_name' => $user->name,
                    'trade_id'    => $trade->id,
                ]);
            }
            $trade->save();

            return $this->commitReturn($trade);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approves a trade in the admin panel.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Trade
     */
    public function approveTrade($data, $user) {
        DB::beginTransaction();

        try {
            // 1. check that the trade exists
            // 2. check that the trade is open
            if (!isset($data['trade'])) {
                $trade = Trade::where('status', 'Pending')->where('id', $data['id'])->first();
            } else {
                $trade = $data['trade'];
            }
            if (!$trade) {
                throw new \Exception('Invalid trade.');
            }

            if ($this->creditAttachments($trade, $data)) {
                Notifications::create('TRADE_COMPLETED', $trade->sender, [
                    'trade_id' => $trade->id,
                ]);
                Notifications::create('TRADE_COMPLETED', $trade->recipient, [
                    'trade_id' => $trade->id,
                ]);

                if (!$this->logAdminAction($user, 'Approved Trade', 'Approved trade <a href="'.$trade->url.'">#'.$trade->id.'</a>')) {
                    throw new \Exception('Failed to log admin action.');
                }

                $trade->status = 'Completed';
                $trade->staff_id = $user->id;
                $trade->save();

                return $this->commitReturn($trade);
            } else {
                throw new \Exception('Failed to credit trade attachments.');
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Rejects a trade in the admin panel.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Trade
     */
    public function rejectTrade($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['trade'])) {
                $trade = Trade::where('status', 'Pending')->where('id', $data['id'])->first();
            } else {
                $trade = $data['trade'];
            }
            if (!$trade) {
                throw new \Exception('Invalid trade.');
            }

            if ($this->returnAttachments($trade)) {
                Notifications::create('TRADE_REJECTED', $trade->sender, [
                    'trade_id' => $trade->id,
                ]);
                Notifications::create('TRADE_REJECTED', $trade->recipient, [
                    'trade_id' => $trade->id,
                ]);

                if (!$this->logAdminAction($user, 'Rejected Trade', 'Rejected trade <a href="'.$trade->url.'">#'.$trade->id.'</a>')) {
                    throw new \Exception('Failed to log admin action.');
                }

                $trade->reason = $data['reason'] ?? '';
                $trade->status = 'Rejected';
                $trade->staff_id = $user->id;
                $trade->save();

                return $this->commitReturn($trade);
            } else {
                throw new \Exception('Failed to return trade attachments.');
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles modification of assets on the user's side of a trade.
     *
     * @param Trade $trade
     * @param array $data
     * @param User  $user
     *
     * @return array|bool
     */
    private function handleTradeAssets($trade, $data, $user) {
        DB::beginTransaction();
        try {
            $tradeData = $trade->data;
            $isSender = $trade->sender_id == $user->id;
            $type = ($isSender ? 'sender' : (($trade->recipient_id == $user->id) ? 'recipient' : null));
            if (!$type) {
                throw new \Exception('User not found.');
            }
            // First return any item stacks attached to the trade

            if (isset($tradeData[$type]['user_items'])) {
                foreach ($tradeData[$type]['user_items'] as $userItemId=> $quantity) {
                    $quantity = (int) $quantity;
                    $userItemRow = UserItem::find($userItemId);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                    }
                    if ($userItemRow->trade_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                    }
                    $userItemRow->trade_count -= $quantity;
                    $userItemRow->save();
                }
            }

            // Also return any currency attached to the trade
            // This is stored in the data attribute
            $currencyManager = new CurrencyManager;
            if (isset($tradeData[$type]['currencies'])) {
                foreach ($tradeData[$type]['currencies'] as $currencyId=> $quantity) {
                    $quantity = (int) $quantity;
                    $currencyManager->creditCurrency(null, $user, null, null, $currencyId, $quantity);
                }
            }

            // Unattach characters too
            Character::where('trade_id', $trade->id)->where('user_id', $user->id)->update(['trade_id' => null]);

            $userAssets = createAssetsArray();
            $assetCount = 0;
            $assetLimit = config('lorekeeper.settings.trade_asset_limit');

            // Attach items. Technically, the user doesn't lose ownership of the item - we're just adding an additional holding field.
            // Unlike for design updates, we're keeping track of attached items here.
            if (isset($data['stack_id'])) {
                foreach ($data['stack_id'] as $stackId) {
                    $stack = UserItem::with('item')->find($stackId);
                    if (!$stack || $stack->user_id != $user->id) {
                        throw new \Exception('Invalid item selected.');
                    }
                    if (!isset($data['stack_quantity'][$stackId])) {
                        throw new \Exception('Invalid quantity selected.');
                    }
                    if (!$stack->item->allow_transfer || isset($stack->data['disallow_transfer'])) {
                        throw new \Exception('One or more of the selected items cannot be transferred.');
                    }
                    $stack->trade_count += intval($data['stack_quantity'][$stackId]);
                    $stack->save();

                    addAsset($userAssets, $stack, intval($data['stack_quantity'][$stackId]));
                    $assetCount++;
                }
            }
            if ($assetCount > $assetLimit) {
                throw new \Exception("You may only include a maximum of {$assetLimit} things in a trade.");
            }

            // Attach currencies. Character currencies cannot be attached to trades, so we're just checking the user's bank.
            if (isset($data['currency_id'])) {
                if ($user->id != $trade->sender_id && $user->id != $trade->recipient_id) {
                    throw new \Exception('Error attaching currencies to this trade.');
                }
                // dd([$data['currency_id'], $data['currency_quantity']]);
                $data['currency_id'] = $data['currency_id']['user-'.$user->id];
                $data['currency_quantity'] = $data['currency_quantity']['user-'.$user->id];
                foreach ($data['currency_id'] as $key=> $currencyId) {
                    $currency = Currency::where('allow_user_to_user', 1)->where('id', $currencyId)->first();
                    if (!$currency) {
                        throw new \Exception('Invalid currency selected.');
                    }
                    if (!$currencyManager->debitCurrency($user, null, null, null, $currency, intval($data['currency_quantity'][$key]))) {
                        throw new \Exception('Invalid currency/quantity selected.');
                    }

                    addAsset($userAssets, $currency, intval($data['currency_quantity'][$key]));
                    $assetCount++;
                }
            }
            if ($assetCount > $assetLimit) {
                throw new \Exception("You may only include a maximum of {$assetLimit} things in a trade.");
            }

            // Attach characters.
            if (isset($data['character_id'])) {
                foreach ($data['character_id'] as $characterId) {
                    $character = Character::where('id', $characterId)->where('user_id', $user->id)->first();
                    if (!$character) {
                        throw new \Exception('Invalid character selected.');
                    }
                    if (!$character->is_sellable && !$character->is_tradeable && !$character->is_giftable) {
                        throw new \Exception('One or more of the selected characters cannot be transferred.');
                    }
                    if (CharacterTransfer::active()->where('character_id', $character->id)->exists()) {
                        throw new \Exception('One or more of the selected characters is already pending a character transfer.');
                    }
                    if ($character->trade_id) {
                        throw new \Exception('One or more of the selected characters is already in a trade.');
                    }
                    if ($character->designUpdate()->active()->exists()) {
                        throw new \Exception('One or more of the selected characters has an active design update. Please wait for it to be processed, or delete it.');
                    }
                    if ($character->transferrable_at && $character->transferrable_at->isFuture()) {
                        throw new \Exception('One or more of the selected characters is still on transfer cooldown and cannot be transferred.');
                    }

                    $character->trade_id = $trade->id;
                    $character->save();

                    addAsset($userAssets, $character, 1);
                    $assetCount++;
                }
            }
            if ($assetCount > $assetLimit) {
                throw new \Exception("You may only include a maximum of {$assetLimit} things in a trade.");
            }

            return $this->commitReturn([($isSender ? 'sender' : 'recipient') => $userAssets]);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Returns trade attachments to their owners.
     *
     * @param Trade $trade
     *
     * @return bool
     */
    private function returnAttachments($trade) {
        DB::beginTransaction();

        try {
            $tradeData = $trade->data;
            // Return all added items/currency/characters
            foreach (['sender', 'recipient'] as $type) {
                if (isset($tradeData[$type]['user_items'])) {
                    foreach ($tradeData[$type]['user_items'] as $userItemId => $quantity) {
                        $quantity = (int) $quantity;
                        $userItemRow = UserItem::find($userItemId);
                        if (!$userItemRow) {
                            throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                        }
                        if ($userItemRow->trade_count < $quantity) {
                            throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                        }
                        $userItemRow->trade_count -= $quantity;
                        $userItemRow->save();
                    }
                }
            }

            Character::where('trade_id', $trade->id)->update(['trade_id' => null]);
            $currencyManager = new CurrencyManager;
            foreach (['sender', 'recipient'] as $type) {
                if (isset($tradeData[$type]['currencies'])) {
                    foreach ($tradeData[$type]['currencies'] as $currencyId => $quantity) {
                        $quantity = (int) $quantity;
                        $currency = Currency::find($currencyId);
                        if (!$currency) {
                            throw new \Exception('Cannot return an invalid currency. ('.$currencyId.')');
                        }
                        if (!$currencyManager->creditCurrency(null, $trade->{$type}, null, null, $currency, $quantity)) {
                            throw new \Exception('Could not return currency to user. ('.$currencyId.')');
                        }
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credits trade attachments to their new owners.
     *
     * @param Trade $trade
     * @param array $data
     *
     * @return bool
     */
    private function creditAttachments($trade, $data = []) {
        DB::beginTransaction();

        try {
            $inventoryManager = new InventoryManager;

            // Get all items
            $senderStacks = null;
            $recipientStacks = null;
            if (isset($trade->data['sender']['user_items'])) {
                $senderStacks = UserItem::find(array_keys($trade->data['sender']['user_items']));
            }
            if (isset($trade->data['recipient']['user_items'])) {
                $recipientStacks = UserItem::find(array_keys($trade->data['recipient']['user_items']));
            }

            if ($senderStacks) {
                foreach ($senderStacks as $stack) {
                    $quantity = $trade->data['sender']['user_items'][$stack->id];
                    $quantity = (int) $quantity;
                    $inventoryManager->moveStack($trade->sender, $trade->recipient, 'Trade', ['data' => 'Received in trade [<a href="'.$trade->url.'">#'.$trade->id.'</a>]'], $stack, $quantity);
                    $userItemRow = UserItem::find($stack->id);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemRow->id.')');
                    }
                    if ($userItemRow->trade_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemRow->id.')');
                    }
                    $userItemRow->trade_count -= $quantity;
                    $userItemRow->save();
                }
            }
            if ($recipientStacks) {
                foreach ($recipientStacks as $stack) {
                    $quantity = $trade->data['recipient']['user_items'][$stack->id];
                    $quantity = (int) $quantity;
                    $inventoryManager->moveStack($trade->recipient, $trade->sender, 'Trade', ['data' => 'Received in trade [<a href="'.$trade->url.'">#'.$trade->id.'</a>]'], $stack, $quantity);
                    $userItemRow = UserItem::find($stack->id);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemRow->id.')');
                    }
                    if ($userItemRow->trade_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemRow->id.')');
                    }
                    $userItemRow->trade_count -= $quantity;
                    $userItemRow->save();
                }
            }
            $characterManager = new CharacterManager;

            // Transfer characters
            $cooldowns = $data['cooldowns'] ?? [];
            $defaultCooldown = Settings::get('transfer_cooldown');

            $senderCharacters = Character::where('user_id', $trade->sender_id)->where('trade_id', $trade->id)->get();
            $recipientCharacters = Character::where('user_id', $trade->recipient_id)->where('trade_id', $trade->id)->get();

            foreach ($senderCharacters as $character) {
                $characterManager->moveCharacter($character, $trade->recipient, 'Trade [<a href="'.$trade->url.'">#'.$trade->id.'</a>]', $cooldowns[$character->id] ?? $defaultCooldown, 'Transferred in trade');
            }

            foreach ($recipientCharacters as $character) {
                $characterManager->moveCharacter($character, $trade->sender, 'Trade [<a href="'.$trade->url.'">#'.$trade->id.'</a>]', $cooldowns[$character->id] ?? $defaultCooldown, 'Transferred in trade');
            }

            Character::where('trade_id', $trade->id)->update(['trade_id' => null]);

            // Transfer currency
            $tradeData = $trade->data;
            $currencyManager = new CurrencyManager;
            foreach (['sender', 'recipient'] as $type) {
                $recipientType = ($type == 'sender') ? 'recipient' : 'sender';
                if (isset($tradeData[$type]['currencies'])) {
                    foreach ($tradeData[$type]['currencies'] as $currencyId => $quantity) {
                        $quantity = (int) $quantity;
                        $currency = Currency::find($currencyId);
                        if (!$currency) {
                            throw new \Exception('Cannot credit an invalid currency. ('.$currencyId.')');
                        }
                        if (!$currencyManager->creditCurrency($trade->{$type}, $trade->{$recipientType}, 'Trade', 'Received in trade [<a href="'.$trade->url.'">#'.$trade->id.'</a>]', $currency, $quantity)) {
                            throw new \Exception('Could not credit currency. ('.$currencyId.')');
                        }
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
