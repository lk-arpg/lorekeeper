<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurrencyController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Currency Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of currency categories and currencies.
    |
    */

    /**********************************************************************************************

        CURRENCY CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the currency category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.currencies.currency_categories', [
            'categories' => CurrencyCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create currency category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCurrencyCategory() {
        return view('admin.currencies.create_edit_currency_category', [
            'category' => new CurrencyCategory,
        ]);
    }

    /**
     * Shows the edit currency category page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCurrencyCategory($id) {
        $category = CurrencyCategory::find($id);
        if (!$category) {
            abort(404);
        }

        return view('admin.currencies.create_edit_currency_category', [
            'category' => $category,
        ]);
    }

    /**
     * Creates or edits a currency category.
     *
     * @param App\Services\CurrencyService $service
     * @param int|null                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCurrencyCategory(Request $request, CurrencyService $service, $id = null) {
        $id ? $request->validate(CurrencyCategory::$updateRules) : $request->validate(CurrencyCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_visible',
        ]);
        if ($id && $service->updateCurrencyCategory(CurrencyCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        } elseif (!$id && $category = $service->createCurrencyCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();

            return redirect()->to('admin/data/currency-categories/edit/'.$category->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the currency category deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCurrencyCategory($id) {
        $category = CurrencyCategory::find($id);

        return view('admin.currencies._delete_currency_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes a currency category.
     *
     * @param App\Services\CurrencyService $service
     * @param int                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCurrencyCategory(Request $request, CurrencyService $service, $id) {
        if ($id && $service->deleteCurrencyCategory(CurrencyCategory::find($id), Auth::user())) {
            flash('Category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/currency-categories');
    }

    /**
     * Sorts currency categories.
     *
     * @param App\Services\CurrencyService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCurrencyCategory(Request $request, CurrencyService $service) {
        if ($service->sortCurrencyCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**********************************************************************************************

        CURRENCIES

    **********************************************************************************************/

    /**
     * Shows the currency index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCurrencyIndex() {
        return view('admin.currencies.currencies', [
            'currencies' => Currency::paginate(30),
        ]);
    }

    /**
     * Shows the create currency page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCurrency() {
        return view('admin.currencies.create_edit_currency', [
            'currency'   => new Currency,
            'categories' => CurrencyCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit currency page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCurrency($id) {
        $currency = Currency::find($id);
        if (!$currency) {
            abort(404);
        }

        return view('admin.currencies.create_edit_currency', [
            'currency'   => $currency,
            'categories' => CurrencyCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'currencies' => Currency::where('id', '!=', $id)->get()->sortBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits a currency.
     *
     * @param App\Services\CharacterCategoryService $service
     * @param int|null                              $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCurrency(Request $request, CurrencyService $service, $id = null) {
        $id ? $request->validate(Currency::$updateRules) : $request->validate(Currency::$createRules);
        $data = $request->only([
            'is_user_owned', 'is_character_owned',
            'name', 'abbreviation', 'description', 'currency_category_id',
            'is_displayed', 'allow_user_to_user', 'allow_user_to_character', 'allow_character_to_user',
            'icon', 'image', 'remove_icon', 'remove_image',
            'conversion_id', 'rate', 'is_visible',
        ]);
        if ($id && $service->updateCurrency(Currency::find($id), $data, Auth::user())) {
            flash('Currency updated successfully.')->success();
        } elseif (!$id && $currency = $service->createCurrency($data, Auth::user())) {
            flash('Currency created successfully.')->success();

            return redirect()->to('admin/data/currencies/edit/'.$currency->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the currency deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCurrency($id) {
        $currency = Currency::find($id);

        return view('admin.currencies._delete_currency', [
            'currency' => $currency,
        ]);
    }

    /**
     * Deletes a currency.
     *
     * @param App\Services\CharacterCategoryService $service
     * @param int                                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCurrency(Request $request, CurrencyService $service, $id) {
        if ($id && $service->deleteCurrency(Currency::find($id), Auth::user())) {
            flash('Currency deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/currencies');
    }

    /**
     * Shows the sort currency page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSort() {
        return view('admin.currencies.sort', [
            'userCurrencies'      => Currency::where('is_user_owned', 1)->orderBy('sort_user', 'DESC')->get(),
            'characterCurrencies' => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->get(),
        ]);
    }

    /**
     * Sorts currencies.
     *
     * @param App\Services\CharacterCategoryService $service
     * @param string                                $type
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCurrency(Request $request, CurrencyService $service, $type) {
        if ($service->sortCurrency($request->get('sort'), $type)) {
            flash('Currency order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
