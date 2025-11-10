<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Shop\Shop;
use App\Models\Shop\ShopStock;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Shop Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of shops and shop stock.
    |
    */

    /**
     * Shows the shop index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.shops.shops', [
            'shops' => Shop::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create shop page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateShop() {
        // get all items where they have a tag 'coupon'
        $coupons = Item::whereHas('tags', function ($query) {
            $query->where('tag', 'coupon')->where('is_active', 1);
        })->orderBy('name')->pluck('name', 'id');

        return view('admin.shops.create_edit_shop', [
            'shop'    => new Shop,
            'items'   => Item::orderBy('name')->pluck('name', 'id'),
            'coupons' => $coupons,
        ]);
    }

    /**
     * Shows the edit shop page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditShop($id) {
        $shop = Shop::find($id);
        if (!$shop) {
            abort(404);
        }

        // get all items where they have a tag 'coupon'
        $coupons = Item::released()->whereHas('tags', function ($query) {
            $query->where('tag', 'coupon');
        })->orderBy('name')->pluck('name', 'id');

        return view('admin.shops.create_edit_shop', [
            'shop'       => $shop,
            'items'      => Item::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::orderBy('name')->pluck('name', 'id'),
            'coupons'    => $coupons,
        ]);
    }

    /**
     * Creates or edits a shop.
     *
     * @param App\Services\ShopService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditShop(Request $request, ShopService $service, $id = null) {
        $id ? $request->validate(Shop::$updateRules) : $request->validate(Shop::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_active', 'is_staff', 'use_coupons', 'is_fto', 'allowed_coupons', 'is_timed_shop', 'start_at', 'end_at',
            'is_hidden', 'shop_days', 'shop_months',
        ]);
        if ($id && $service->updateShop(Shop::find($id), $data, Auth::user())) {
            flash('Shop updated successfully.')->success();
        } elseif (!$id && $shop = $service->createShop($data, Auth::user())) {
            flash('Shop created successfully.')->success();

            return redirect()->to('admin/data/shops/edit/'.$shop->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * loads the create stock modal.
     *
     * @param mixed $id
     */
    public function getCreateShopStock($id) {
        $shop = Shop::find($id);
        if (!$shop) {
            abort(404);
        }

        return view('admin.shops._stock_modal', [
            'shop'       => $shop,
            'currencies' => Currency::orderBy('name')->pluck('name', 'id'),
            'stock'      => new ShopStock,
        ]);
    }

    /**
     * loads the edit stock modal.
     *
     * @param mixed $id
     */
    public function getEditShopStock($id) {
        $stock = ShopStock::find($id);
        if (!$stock) {
            abort(404);
        }
        // get base modal from type using asset helper
        $type = $stock->stock_type;
        $model = getAssetModelString(strtolower($type));

        // check if categories exist for this model ($model.'Category')
        $categoryClass = $model.'Category';
        if (class_exists($categoryClass)) {
            // map the categories to be name-id
            $categories = $categoryClass::orderBy('name')->get()->mapWithKeys(function ($category) {
                return [$category->id.'-category' => $category->name];
            });
            $items = [
                $type            => $model::orderBy('name')->pluck('name', 'id')->toArray() + ['random' => 'Random '.$type],
                $type.'Category' => $categories->toArray(),
            ];
        } else {
            if ($model == '\App\Models\Raffle\Raffle') {
                $items = $model::orderBy('name')->where('is_active', '!=', 2)->pluck('name', 'id')->toArray();
            } else {
                $items = $model::orderBy('name')->pluck('name', 'id')->toArray();
            }
        }

        return view('admin.shops._stock_modal', [
            'shop'       => $stock->shop,
            'stock'      => $stock,
            'items'      => $items,
        ]);
    }

    /**
     * gets stock of a certain type.
     */
    public function getShopStockType(Request $request) {
        $type = $request->input('type');
        if (!$type) {
            return null;
        }
        // get base modal from type using asset helper
        $model = getAssetModelString(strtolower($type));

        // check if categories exist for this model ($model.'Category')
        $categoryClass = $model.'Category';
        if (class_exists($categoryClass)) {
            // map the categories to be name-id
            $categories = $categoryClass::orderBy('name')->get()->mapWithKeys(function ($category) {
                return [$category->id.'-category' => $category->name];
            });
            $items = [
                $type            => $model::orderBy('name')->pluck('name', 'id')->toArray() + ['random' => 'Random '.$type],
                $type.'Category' => $categories->toArray(),
            ];
        } else {
            if ($model == '\App\Models\Raffle\Raffle') {
                $items = $model::orderBy('name')->where('is_active', '!=', 2)->pluck('name', 'id')->toArray();
            } else {
                $items = $model::orderBy('name')->pluck('name', 'id')->toArray();
            }
        }

        return view('admin.shops._stock_item', [
            'items' => $items,
        ]);
    }

    /**
     * gets the type of a cost for a stock.
     */
    public function getShopStockCostType(Request $request) {
        $type = $request->input('type');
        if (!$type) {
            return null;
        }
        // get base modal from type using asset helper
        $model = getAssetModelString(strtolower($type));

        return view('admin.shops._stock_cost', [
            'costItems' => $model::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Edits a shop's stock.
     *
     * @param App\Services\ShopService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditShopStock(Request $request, ShopService $service, $id) {
        $data = $request->only([
            'shop_id', 'item_id', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'purchase_limit', 'purchase_limit_timeframe', 'is_fto', 'stock_type', 'is_visible',
            'restock', 'restock_quantity', 'restock_interval', 'range', 'disallow_transfer', 'is_timed_stock', 'stock_start_at', 'stock_end_at',
            'cost_type', 'cost_quantity', 'cost_id', 'group', 'can_group_use_coupon', 'stock_days', 'stock_months',
        ]);
        if ($service->editShopStock(ShopStock::find($id), $data, Auth::user())) {
            flash('Shop stock updated successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Edits a shop's stock.
     *
     * @param App\Services\ShopService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateShopStock(Request $request, ShopService $service, $id) {
        $data = $request->only([
            'shop_id', 'item_id', 'currency_id', 'cost', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'purchase_limit', 'purchase_limit_timeframe', 'is_fto', 'stock_type', 'is_visible',
            'restock', 'restock_quantity', 'restock_interval', 'range', 'disallow_transfer', 'is_timed_stock', 'stock_start_at', 'stock_end_at',
            'cost_type', 'cost_quantity', 'cost_id', 'group', 'stock_days', 'stock_months',
        ]);
        if ($service->createShopStock(Shop::find($id), $data, Auth::user())) {
            flash('Shop stock updated successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the stock deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShopStock($id) {
        $stock = ShopStock::find($id);

        return view('admin.shops._delete_stock', [
            'stock' => $stock,
        ]);
    }

    /**
     * Deletes a stock.
     *
     * @param App\Services\StockService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShopStock(Request $request, ShopService $service, $id) {
        $stock = ShopStock::find($id);
        $shop = $stock->shop;
        if ($id && $service->deleteStock($stock)) {
            flash('Stock deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/shops/edit/'.$shop->id);
    }

    /**
     * Gets the shop deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShop($id) {
        $shop = Shop::find($id);

        return view('admin.shops._delete_shop', [
            'shop' => $shop,
        ]);
    }

    /**
     * Deletes a shop.
     *
     * @param App\Services\ShopService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShop(Request $request, ShopService $service, $id) {
        if ($id && $service->deleteShop(Shop::find($id))) {
            flash('Shop deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/shops');
    }

    /**
     * Sorts shops.
     *
     * @param App\Services\ShopService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortShop(Request $request, ShopService $service) {
        if ($service->sortShop($request->get('sort'))) {
            flash('Shop order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
