<?php

namespace App\Services;

use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Character\CharacterTransfer;
use App\Models\Currency\Currency;
use App\Models\Trade\TradeListing;
use App\Models\User\User;
use App\Models\User\UserItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TradeListingManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Trade Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of trade data.
    |
    */

    /**
     * Creates a new trade listing.
     *
     * @param array      $data
     * @param User       $user
     * @param mixed|null $id
     *
     * @return bool|TradeListing
     */
    public function createEditTradeListing($data, $user, $id = null) {
        DB::beginTransaction();
        try {
            if (!isset($data['contact'])) {
                throw new \Exception('Please enter your preferred method(s) of contact.');
            }
            if ($id) {
                $listing = TradeListing::find($id);
                if (!$listing) {
                    throw new \Exception('Invalid trade listing.');
                }
                if (!$listing->isActive) {
                    throw new \Exception('This listing is already expired.');
                }
                if (!$listing->user->id == Auth::user()->id && !Auth::user()->hasPower('manage_submissions')) {
                    throw new \Exception("You can't edit this listing.");
                }
                $listing->update([
                    'title'    => $data['title'] ?? null,
                    'comments' => $data['comments'] ?? null,
                    'contact'  => $data['contact'],
                ]);
            } else {
                if (TradeListing::where('user_id', $user->id)->where('expires_at', '>', Carbon::now())->count() > Settings::get('trade_listing_limit')) {
                    throw new \Exception('You already have the maximum number of active trade listings. Please wait for them to expire before creating a new one.');
                }
                $listing = TradeListing::create([
                    'title'    => $data['title'] ?? null,
                    'user_id'  => $user->id,
                    'comments' => $data['comments'] ?? null,
                    'contact'  => $data['contact'],
                ]);
            }

            $listingData = [];
            if (!$seekingData = $this->handleSeekingAssets($listing, $data, $user)) {
                throw new \Exception('Error attaching sought attachments.');
            } else {
                $listingData['seeking'] = getDataReadyAssets($seekingData);
            }

            if (!$offeringData = $this->handleOfferingAssets($listing, $data, $user)) {
                throw new \Exception('Error attaching offered attachments.');
            } else {
                $listingData['offering'] = getDataReadyAssets($offeringData);
            }

            if ($data['offering_etc'] || $data['seeking_etc']) {
                $listingData['offering_etc'] = $data['offering_etc'] ?? null;
                $listingData['seeking_etc'] = $data['seeking_etc'] ?? null;
            }

            $listing->expires_at = $id ? $listing->expires_at : Carbon::now()->addDays(Settings::get('trade_listing_duration'));
            $listing->created_at = $id ? $listing->created_at : Carbon::now();
            $listing->updated_at = Carbon::now();
            $listing->data = $listingData;
            $listing->save();

            if (!$listing->data) {
                throw new \Exception("Please enter what you're seeking and offering.");
            }
            if (!isset($listing->data['seeking']) && !isset($listing->data['seeking_etc'])) {
                throw new \Exception("Please enter what you're seeking.");
            }
            if (!isset($listing->data['offering']) && !isset($listing->data['offering_etc'])) {
                throw new \Exception("Please enter what you're offering.");
            }

            return $this->commitReturn($listing);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Marks a trade listing as expired.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|TradeListing
     */
    public function markExpired($data, $user) {
        DB::beginTransaction();
        try {
            $listing = TradeListing::find($data['id']);
            if (!$listing) {
                throw new \Exception('Invalid trade listing.');
            }
            if (!$listing->isActive) {
                throw new \Exception('This listing is already expired.');
            }
            if (!$listing->user->id == Auth::user()->id && !Auth::user()->hasPower('manage_submissions')) {
                throw new \Exception("You can't edit this listing.");
            }

            $listing->expires_at = Carbon::now();
            $listing->save();

            return $this->commitReturn($listing);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles recording of assets on the seeking side of a trade listing, as well as initial validation.
     *
     * @param TradeListing $listing
     * @param array        $data
     * @param mixed        $user
     *
     * @return array|bool
     */
    private function handleSeekingAssets($listing, $data, $user) {
        DB::beginTransaction();
        try {
            $seekingAssets = createAssetsArray();
            $assetCount = 0;
            $assetLimit = config('lorekeeper.settings.trade_asset_limit');

            if (isset($data['rewardable_type'])) {
                foreach ($data['rewardable_type'] as $key=>$type) {
                    $model = getAssetModelString(strtolower($type));
                    $asset = $model::find($data['rewardable_id'][$key]);
                    if (!$asset) {
                        throw new \Exception("Invalid {$type} selected.");
                    }

                    if (!canTradeAsset($type, $asset)) {
                        throw new \Exception("One or more of the selected {$type}s cannot be traded.");
                    }

                    if ($type == 'Item') {
                        if (!$asset->allow_transfer) {
                            throw new \Exception('One or more of the selected items cannot be transferred.');
                        }
                    } elseif ($type == 'Currency') {
                        if (!$asset->is_user_owned) {
                            throw new \Exception('One or more of the selected currencies cannot be held by users.');
                        }
                        if (!$asset->allow_user_to_user) {
                            throw new \Exception('One or more of the selected currencies cannot be traded.');
                        }
                    }

                    if ($data['quantity'][$key] < 1) {
                        throw new \Exception('You must select a quantity of at least 1 for each asset.');
                    }

                    addAsset($seekingAssets, $asset, $data['quantity'][$key]);
                    $assetCount += 1;
                }
            }
            if ($assetCount > $assetLimit) {
                throw new \Exception("You may only include a maximum of {$assetLimit} things in a listing.");
            }

            return $this->commitReturn($seekingAssets);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles recording of assets on the user's side of a trade listing, as well as initial validation.
     *
     * @param TradeListing $listing
     * @param array        $data
     * @param User         $user
     *
     * @return array|bool
     */
    private function handleOfferingAssets($listing, $data, $user) {
        DB::beginTransaction();
        try {
            $userAssets = createAssetsArray();
            $assetCount = 0;
            $assetLimit = config('lorekeeper.settings.trade_asset_limit');

            // Attach items. They are not even held, merely recorded for display on the listing.
            if (isset($data['stack_id'])) {
                foreach ($data['stack_id'] as $key=>$stackId) {
                    $stack = UserItem::with('item')->find($stackId);
                    if (!$stack || $stack->user_id != $user->id) {
                        throw new \Exception('Invalid item selected.');
                    }
                    if (!$stack->item->allow_transfer || isset($stack->data['disallow_transfer'])) {
                        throw new \Exception('One or more of the selected items cannot be transferred.');
                    }

                    if ($data['stack_quantity'][$stackId] < 1) {
                        throw new \Exception('You must select a quantity of at least 1 for each item.');
                    }

                    addAsset($userAssets, $stack, $data['stack_quantity'][$stackId]);
                    $assetCount++;
                }
            }
            if ($assetCount > $assetLimit) {
                throw new \Exception("You may only include a maximum of {$assetLimit} things in a listing.");
            }

            // Attach currencies. Character currencies cannot be attached to trades, so we're just checking the user's bank.
            if (isset($data['offer_currency_ids'])) {
                foreach ($data['offer_currency_ids'] as $key=>$currencyId) {
                    $currency = Currency::where('allow_user_to_user', 1)->where('id', $currencyId)->first();
                    if (!$currency) {
                        throw new \Exception('Invalid currency selected.');
                    }

                    addAsset($userAssets, $currency, 1);
                    $assetCount++;
                }
            }
            if ($assetCount > $assetLimit) {
                throw new \Exception("You may only include a maximum of {$assetLimit} things in a listing.");
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

                    addAsset($userAssets, $character, 1);
                    $assetCount++;
                }
            }
            if ($assetCount > $assetLimit) {
                throw new \Exception("You may only include a maximum of {$assetLimit} things in a listing.");
            }

            return $this->commitReturn($userAssets);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
