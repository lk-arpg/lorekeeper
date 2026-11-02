<?php

namespace App\Http\Controllers\Users;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Models\User\User;
use App\Models\User\UserCurrency;
use App\Services\CurrencyManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Bank Controller
    |--------------------------------------------------------------------------
    |
    | Handles displaying of the user's bank.
    |
    */

    /**
     * Shows the user's bank page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('home.bank', [
            'currencyOptions' => Currency::where('allow_user_to_user', 1)->where('is_user_owned', 1)->whereIn('id', UserCurrency::where('user_id', Auth::user()->id)->pluck('currency_id')->toArray())->orderBy('sort_user', 'DESC')->pluck('name', 'id')->toArray(),
            'userOptions'     => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            // only get currency with currency_conversions relationship
            'convertOptions'  => Currency::where('is_user_owned', 1)->whereHas('conversions')->orderBy('sort_user', 'DESC')->pluck('name', 'id')->toArray(),
            'canTransfer'     => Settings::get('can_transfer_currency_directly'),
        ]);
    }

    /**
     * Transfers currency from the user to another.
     *
     * @param App\Services\CurrencyManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTransfer(Request $request, CurrencyManager $service) {
        if ($service->transferCurrency(Auth::user(), User::visible()->where('id', $request->get('user_id'))->first(), Currency::where('allow_user_to_user', 1)->where('id', $request->get('currency_id'))->first(), $request->get('quantity'))) {
            flash('Currency transferred successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the currency conversion form for the user.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getConvertCurrency($id) {
        $currency = Currency::where('is_user_owned', 1)->where('id', $id)->first();
        $convertOptions = Currency::whereIn('id', $currency->conversions->pluck('conversion_id')->toArray())->orderBy('sort_user', 'DESC')->pluck('name', 'id')->toArray();

        return view('home._bank_convert', [
            'convertOptions' => $convertOptions,
        ]);
    }

    /**
     * Gets the currency conversion rate for the user.
     *
     * @param mixed $currency_id
     * @param mixed $conversion_id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getConvertCurrencyRate($currency_id, $conversion_id) {
        $currency = Currency::where('is_user_owned', 1)->where('id', $currency_id)->first();

        return $currency->conversions()->where('conversion_id', $conversion_id)->first()->ratio();
    }

    /**
     * Converts currency from one type to another.
     *
     * @param App\Services\CurrencyManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postConvertCurrency(Request $request, CurrencyManager $service) {
        $data = $request->only(['currency_id', 'conversion_id', 'quantity']);
        if ($service->convertCurrency(Currency::find($data['currency_id']), Currency::find($data['conversion_id']), $data['quantity'], Auth::user())) {
            flash('Currency converted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
