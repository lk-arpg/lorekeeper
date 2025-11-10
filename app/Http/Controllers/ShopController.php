<?php

namespace App\Http\Controllers;

use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\Shop\Shop;
use App\Models\Shop\ShopLog;
use App\Models\Shop\ShopStock;
use App\Models\User\UserItem;
use App\Services\LimitManager;
use App\Services\ShopManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Shop Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing the shop index, shops and purchasing from shops.
    |
    */

    /**
     * Shows the shop index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('shops.index', [
            'shops' => Shop::where('is_active', 1)->where('is_hidden', 0)->orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows a shop.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getShop($id) {
        $shop = Shop::where('id', $id)->where('is_active', 1)->first();

        if (!$shop) {
            abort(404);
        }

        if ($shop->is_staff) {
            if (!Auth::check()) {
                abort(404);
            }
            if (!Auth::user()->isStaff) {
                abort(404);
            }
        }

        if (count(getLimits($shop))) {
            if (!Auth::check()) {
                flash('You must be logged in to enter this shop.')->error();

                return redirect()->to('shops');
            }

            $limitService = new LimitManager;
            if (!$limitService->checkLimits($shop)) {
                flash($limitService->errors()->getMessages()['error'][0])->error();

                return redirect()->to('shops');
            }
        }

        if ($shop->is_fto) {
            if (!Auth::check()) {
                flash('You must be logged in to enter this shop.')->error();

                return redirect()->to('/shops');
            }
            if (!Auth::user()->settings->is_fto && !Auth::user()->isStaff) {
                flash('You must be a FTO to enter this shop.')->error();

                return redirect()->to('/shops');
            }
        }

        // get all types of stock in the shop
        $stock_types = ShopStock::where('shop_id', $shop->id)->pluck('stock_type')->unique();
        $stocks = [];
        foreach ($stock_types as $type) {
            // get the model for the stock type (item, pet, etc)
            $type = strtolower($type);
            $model = getAssetModelString($type);
            // get the category of the stock
            if (!class_exists($model.'Category')) {
                $stock = $shop->displayStock($model, $type)->where('stock_type', $type)->orderBy('name')->get()->groupBy($type.'_category_id');
                $stocks[$type] = $stock;
                continue; // If the category model doesn't exist, skip it
            }
            $stock_category = ($model.'Category')::orderBy('sort', 'DESC')->get();
            // order the stock
            $stock = count($stock_category) ? $shop->displayStock($model, $type)->where('stock_type', $type)
                ->orderByRaw('FIELD('.$type.'_category_id,'.implode(',', $stock_category->pluck('id')->toArray()).')')
                ->orderBy('name')->get()->groupBy($type.'_category_id')
            : $shop->displayStock($model, $type)->where('stock_type', $type)->orderBy('name')->get()->groupBy($type.'_category_id');

            // make it so key "" appears last
            $stock = $stock->sortBy(function ($item, $key) {
                return $key == '' ? 1 : 0;
            });

            $stocks[$type] = $stock;
        }

        return view('shops.shop', [
            'shop'       => $shop,
            'stocks'     => $stocks,
            'shops'      => Shop::where('is_active', 1)->where('is_hidden', 0)->orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Gets the shop stock modal.
     *
     * @param App\Services\ShopManager $service
     * @param int                      $id
     * @param int                      $stockId
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getShopStock(ShopManager $service, $id, $stockId) {
        $shop = Shop::where('id', $id)->where('is_active', 1)->first();
        $stock = ShopStock::where('id', $stockId)->where('shop_id', $id)->first();
        if (!$shop) {
            abort(404);
        }

        if (count(getLimits($shop))) {
            $limitService = new LimitManager;
            if (!$limitService->checkLimits($shop)) {
                flash($limitService->errors()->getMessages()['error'][0])->error();

                return redirect()->to('shops');
            }
        }

        $user = Auth::user();
        $quantityLimit = 0;
        $userPurchaseCount = 0;
        $purchaseLimitReached = false;
        if ($user) {
            $quantityLimit = $service->getStockPurchaseLimit($stock, Auth::user());
            $userPurchaseCount = $service->checkUserPurchases($stock, Auth::user());
            $purchaseLimitReached = $service->checkPurchaseLimitReached($stock, Auth::user());
            $userOwned = $service->getUserOwned($stock, Auth::user());
        }

        if (Auth::check() && $shop->use_coupons) {
            $couponId = ItemTag::where('tag', 'coupon')->where('is_active', 1); // Removed get()
            $itemIds = $couponId->pluck('item_id'); // Could be combined with above
            // get rid of any itemIds that are not in allowed_coupons
            if ($shop->allowed_coupons && count($shop->allowed_coupons)) {
                $itemIds = $itemIds->filter(function ($itemId) use ($shop) {
                    return in_array($itemId, $shop->allowed_coupons);
                });
            }
            $check = UserItem::with('item')->whereIn('item_id', $itemIds)->where('user_id', Auth::user()->id)->where('count', '>', 0)->get()->pluck('item.name', 'id');
        } else {
            $check = null;
        }

        return view('shops._stock_modal', [
            'shop'                 => $shop,
            'stock'                => $stock,
            'userCoupons'          => $check,
            'quantityLimit'        => $quantityLimit,
            'userPurchaseCount'    => $userPurchaseCount,
            'purchaseLimitReached' => $purchaseLimitReached,
            'userOwned'            => $user ? $userOwned : null,
            'inventory'            => $user ? UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->get() : null,
        ]);
    }

    /**
     * Buys an item from a shop.
     *
     * @param App\Services\ShopManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postBuy(Request $request, ShopManager $service) {
        $request->validate(ShopLog::$createRules);
        if ($service->buyStock($request->only(['stock_id', 'shop_id', 'slug', 'bank', 'quantity', 'use_coupon', 'coupon', 'cost_group', 'stack_id', 'stack_quantity']), Auth::user())) {
            flash('Successfully purchased item.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the user's purchase history.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPurchaseHistory() {
        return view('shops.purchase_history', [
            'logs'  => Auth::user()->getShopLogs(0),
            'shops' => Shop::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
        ]);
    }
}
