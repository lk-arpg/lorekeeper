<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Item\Item;
use App\Models\Shop\Shop;
use App\Models\Shop\ShopLog;
use App\Models\Shop\ShopStock;
use App\Models\User\UserItem;
use Illuminate\Support\Facades\DB;
use Settings;

class ShopManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Shop Manager
    |--------------------------------------------------------------------------
    |
    | Handles purchasing of items from shops.
    |
    */

    /**
     * Buys an item from a shop.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return App\Models\Shop\Shop|bool
     */
    public function buyStock($data, $user) {
        DB::beginTransaction();

        try {
            $quantity = ceil($data['quantity']);
            if (!$quantity || $quantity == 0) {
                throw new \Exception('Invalid quantity selected.');
            }

            // Check that the shop exists and is open
            $shop = Shop::where('id', $data['shop_id'])->where('is_active', 1)->first();
            if (!$shop) {
                throw new \Exception('Invalid shop selected.');
            }

            // Check that the stock exists and belongs to the shop
            $shopStock = ShopStock::where('id', $data['stock_id'])->where('shop_id', $data['shop_id'])->first();
            if (!$shopStock) {
                throw new \Exception('Invalid item selected.');
            }

            // Check if the item has a quantity, and if it does, check there is enough stock remaining
            if ($shopStock->is_limited_stock && $shopStock->quantity < $quantity) {
                throw new \Exception('There is insufficient stock to fulfill your request.');
            }

            if (isset($data['cost_group'])) {
                $costs = $shopStock->costs()->where('group', $data['cost_group'])->get();
            } else {
                $costs = $shopStock->costs()->get();
                // make sure that there is not differing groups
                if (count($costs->pluck('group')->unique()) > 1) {
                    throw new \Exception('There are multiple cost groups for this item, please select one.');
                }
            }

            // Check if the user can only buy a limited number of this item, and if it does, check that the user hasn't hit the limit
            if ($shopStock->purchase_limit && $this->checkPurchaseLimitReached($shopStock, $user)) {
                throw new \Exception('You have already purchased the maximum amount of this item you can buy.');
            }

            $coupon = null;
            $couponUserItem = null;
            if (isset($data['use_coupon'])) {
                // check if the the stock is limited stock
                if ($shopStock->is_limited_stock && !Settings::get('limited_stock_coupon_settings')) {
                    throw new \Exception('Sorry! You can\'t use coupons on limited stock items');
                }
                if (!isset($data['coupon'])) {
                    throw new \Exception('Please select a coupon to use.');
                }
                // finding the users tag
                $couponUserItem = UserItem::find($data['coupon']);
                // check if the item id is inside allowed_coupons
                if ($shop->allowed_coupons && count($shop->allowed_coupons) > 0 && !in_array($couponUserItem->item_id, $shop->allowed_coupons)) {
                    throw new \Exception('Sorry! You can\'t use this coupon.');
                }
                // finding bought item
                $item = Item::find($couponUserItem->item_id);
                $tag = $item->tags()->where('tag', 'Coupon')->first();
                $coupon = $tag->data;

                if (!$coupon['discount']) {
                    throw new \Exception('No discount amount set, please contact a site admin before trying to purchase again.');
                }

                // make sure this item isn't free
                if (!$shopStock->costs()->count()) {
                    throw new \Exception('Cannot use a coupon on an item that is free.');
                }

                // if the coupon isn't infinite kill it
                if (!$coupon['infinite']) {
                    if (!(new InventoryManager)->debitStack($user, 'Coupon Used', ['data' => 'Coupon used in purchase of '.$shopStock->item->name.' from '.$shop->name], $couponUserItem, 1)) {
                        throw new \Exception('Failed to remove coupon.');
                    }
                }
            }

            $character = null;
            if ($data['bank'] == 'character') {
                if (!$shopStock->use_character_bank || !$shopStock->currency->is_character_owned) {
                    throw new \Exception("You cannot use a character's bank to pay for this item.");
                }
                if (!$data['slug']) {
                    throw new \Exception('Please enter a character code.');
                }
                $character = Character::where('slug', $data['slug'])->first();
                if (!$character) {
                    throw new \Exception('Please enter a valid character code.');
                }
                if ($character->user_id != $user->id) {
                    throw new \Exception('That character does not belong to you.');
                }
            }

            $baseStockCost = mergeAssetsArrays(createAssetsArray(true), createAssetsArray());
            $userCostAssets = createAssetsArray();
            $characterCostAssets = createAssetsArray(true);
            $selected = [];
            foreach ($costs as $cost) {
                $costQuantity = abs($cost->quantity);
                if ($coupon) { // coupon applies to ALL costs in the selected group.
                    if (!Settings::get('coupon_settings')) {
                        $minus = ($coupon['discount'] / 100) * ($costQuantity * $quantity);
                        $base = ($costQuantity * $quantity);
                        if ($base <= 0) {
                            throw new \Exception('Cannot use a coupon on an item that is free.');
                        }
                        $new = $base - $minus;
                        $costQuantity = round($new);
                    } else {
                        $minus = ($coupon['discount'] / 100) * ($costQuantity);
                        $base = ($costQuantity * $quantity);
                        if ($base <= 0) {
                            throw new \Exception('Cannot use a coupon on an item that is free.');
                        }
                        $new = $base - $minus;
                        $costQuantity = round($new);
                    }
                } else {
                    $costQuantity *= $quantity;
                }

                if ($cost->item->assetType == 'currencies') {
                    if ($data['bank'] == 'user') {
                        if (!$cost->item->is_user_owned) {
                            throw new \Exception('You cannot use your user bank to pay for this item.');
                        }

                        addAsset($userCostAssets, $cost->item, -$costQuantity);
                    } else {
                        if (!$cost->item->is_character_owned) {
                            throw new \Exception("You cannot use a character's bank to pay for this item.");
                        }

                        addAsset($characterCostAssets, $cost->item, -$costQuantity);
                    }
                } elseif ($cost->item->assetType == 'items') {
                    $requiredQuantity = $costQuantity;
                    if (isset($data['stack_id'])) {
                        foreach ($data['stack_id'] as $userItemStackId) {
                            $stack = UserItem::where('id', $userItemStackId)->where('user_id', $user->id)->where('item_id', $cost->item->id)->where('count', '>', '0')->first();
                            if (!$stack) {
                                continue;
                            }

                            $stackQuantity = $data['stack_quantity'][$userItemStackId] ?? $stack->count;
                            $requiredQuantity -= $stackQuantity;
                            $selected[] = [
                                'stack'    => $stack,
                                'quantity' => $stackQuantity,
                            ];
                        }
                    } else {
                        $stacks = UserItem::where('user_id', $user->id)->where('item_id', $cost->item->id)->where('count', '>', '0')->get()->filter(function ($stack) {
                            return $stack->available_quantity > 0;
                        });
                        foreach ($stacks as $stack) {
                            if ($stack->count >= $requiredQuantity) {
                                $selected[] = [
                                    'stack'    => $stack,
                                    'quantity' => $requiredQuantity,
                                ];
                                $requiredQuantity = 0;
                                break;
                            } else {
                                $selected[] = [
                                    'stack'    => $stack,
                                    'quantity' => $stack->count,
                                ];
                                $requiredQuantity -= $stack->count;
                            }
                        }
                    }

                    if ($requiredQuantity > 0) {
                        throw new \Exception('You do not have enough, or have not selected enough, of the required item to purchase this item.');
                    }

                    addAsset($userCostAssets, $cost->item, -$costQuantity);
                } else {
                    addAsset($userCostAssets, $cost->item, -$costQuantity);
                }

                addAsset($baseStockCost, $cost->item, $cost->quantity);
            }

            if (countAssets($userCostAssets) == 0) {
                // the coupon made it free
                // we will manually make the array empty to prevent trying to credit 0 currency
                $userCostAssets = createAssetsArray();
            }

            if ($character) {
                if (!fillCharacterAssets($characterCostAssets, $character, null, 'Shop Purchase', [
                    'data' => 'Purchased '.$shopStock->item->name.' x'.$quantity.' from '.$shop->name.
                    ($coupon ? '. Coupon used: '.$couponUserItem->item->name : ''),
                ])) {
                    throw new \Exception('Failed to purchase item.');
                }
            }
            if (!fillUserAssets($userCostAssets, $user, null, 'Shop Purchase', [
                'data' => 'Purchased '.$shopStock->item->name.' x'.$quantity.' from '.$shop->name.
                ($coupon ? '. Coupon used: '.$couponUserItem->item->name : ''),
            ], $selected)) {
                throw new \Exception('Failed to purchase item - could not debit costs.');
            }

            // If the item has a limited quantity, decrease the quantity
            if ($shopStock->is_limited_stock) {
                $shopStock->quantity -= $quantity;
                $shopStock->save();
            }

            // Add a purchase log
            $shopLog = ShopLog::create([
                'shop_id'      => $shop->id,
                'character_id' => $character ? $character->id : null,
                'user_id'      => $user->id,
                'cost'         => [
                    'base'      => getDataReadyAssets($baseStockCost),
                    'user'      => getDataReadyAssets($userCostAssets),
                    'character' => getDataReadyAssets($characterCostAssets),
                    'coupon'    => $couponUserItem ? $couponUserItem->item->id : null,
                ],
                'stock_type'   => $shopStock->stock_type,
                'item_id'      => $shopStock->item_id,
                'quantity'     => $quantity,
            ]);

            // Give the user the item, noting down 1. whose currency was used (user or character) 2. who purchased it 3. which shop it was purchased from
            $assets = createAssetsArray();
            addAsset($assets, $shopStock->item, $quantity);

            if (!fillUserAssets($assets, null, $user, 'Shop Purchase', [
                'data'  => $shopLog->itemData,
                'notes' => 'Purchased '.format_date($shopLog->created_at),
            ] + ($shopStock->disallow_transfer ? ['disallow_transfer' => true] : []))) {
                throw new \Exception('Failed to purchase item - could not credit item.');
            }

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Checks if the purchase limit for an item from a shop has been reached.
     *
     * @param ShopStock             $shopStock
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function checkPurchaseLimitReached($shopStock, $user) {
        if ($shopStock->purchase_limit > 0) {
            return $this->checkUserPurchases($shopStock, $user) >= $shopStock->purchase_limit;
        }

        return false;
    }

    /**
     * Checks how many times a user has purchased a shop item.
     *
     * @param ShopStock             $shopStock
     * @param \App\Models\User\User $user
     *
     * @return int
     */
    public function checkUserPurchases($shopStock, $user) {
        $date = $shopStock->purchaseLimitDate;
        $shopQuery = ShopLog::where('shop_id', $shopStock->shop_id)
            ->where('item_id', $shopStock->item_id)
            ->where('user_id', $user->id);
        $shopQuery = isset($date) ? $shopQuery->where('created_at', '>=', date('Y-m-d H:i:s', $date)) : $shopQuery;

        // check the costs vs the user's purchase recorded costs
        $shopQuery = $shopQuery->get()->filter(function ($log) use ($shopStock) {
            // if there is no costs, then return true, since free items should also have limits
            if (!count($shopStock->costGroups) && countAssets($log->baseCost) == 0) {
                return true;
            }

            foreach ($shopStock->costGroups as $group => $costs) {
                if (compareAssetArrays($log->baseCost, $costs, false, true)) {
                    return true;
                }
            }

            return false;
        });

        return $shopQuery->sum('quantity');
    }

    /**
     * Gets the purchase limit for an item from a shop.
     *
     * @param mixed $shopStock
     * @param mixed $user
     */
    public function getStockPurchaseLimit($shopStock, $user) {
        $limit = config('lorekeeper.settings.default_purchase_limit');
        if ($shopStock->purchase_limit > 0) {
            $user_purchase_limit = $shopStock->purchase_limit - $this->checkUserPurchases($shopStock, $user);
            if ($user_purchase_limit < $limit) {
                $limit = $user_purchase_limit;
            }
        }
        if ($shopStock->is_limited_stock) {
            if ($shopStock->quantity < $limit) {
                $limit = $shopStock->quantity;
            }
        }

        return $limit;
    }

    /**
     * Gets how many of a shop item a user owns.
     *
     * @param mixed $stock
     * @param mixed $user
     */
    public function getUserOwned($stock, $user) {
        switch (strtolower($stock->stock_type)) {
            case 'item':
                return $user->items()->where('item_id', $stock->item_id)->sum('count');
        }
    }
}
