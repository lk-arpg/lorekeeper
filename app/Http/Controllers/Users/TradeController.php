<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Character\CharacterCategory;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Trade;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\TradeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradeController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Trade Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing the user's trade index, creating and acting on trades.
    |
    */

    /**
     * Shows the user's trades.
     *
     * @param mixed $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex($status = 'open') {
        $user = Auth::user();
        $trades = Trade::with('recipient')->with('sender')->with('staff')->where(function ($query) {
            $query->where('recipient_id', Auth::user()->id)->orWhere('sender_id', Auth::user()->id);
        })->where('status', ucfirst($status))->orderBy('id', 'DESC');

        return view('home.trades.index', [
            'trades' => $trades->paginate(20),
        ]);
    }

    /**
     * Shows a trade.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTrade($id) {
        $trade = Trade::find($id);

        if ($trade->status != 'Completed' && !Auth::user()->hasPower('manage_characters') && !($trade->sender_id == Auth::user()->id || $trade->recipient_id == Auth::user()->id)) {
            $trade = null;
        }

        if (!$trade) {
            abort(404);
        }

        return view('home.trades.trade', [
            'trade'         => $trade,
            'partner'       => (Auth::user()->id == $trade->sender_id) ? $trade->recipient : $trade->sender,
            'senderData'    => isset($trade->data['sender']) ? parseAssetData($trade->data['sender']) : null,
            'recipientData' => isset($trade->data['recipient']) ? parseAssetData($trade->data['recipient']) : null,
            'items'         => Item::all()->keyBy('id'),
        ]);
    }

    /**
     * Shows the trade creation page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateTrade() {
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)
            ->get()
            ->filter(function ($userItem) {
                return $userItem->isTransferrable == true;
            })
            ->sortBy('item.name');

        return view('home.trades.create_trade', [
            'categories'          => ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'item_filter'         => Item::orderBy('name')->get()->keyBy('id'),
            'inventory'           => $inventory,
            'userOptions'         => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'characters'          => Auth::user()->allCharacters()->visible()->tradable()->with('designUpdate')->get(),
            'characterCategories' => CharacterCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'page'                => 'trade',
        ]);
    }

    /**
     * Shows the trade edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditTrade($id) {
        $trade = Trade::where('id', $id)->where(function ($query) {
            $query->where('recipient_id', Auth::user()->id)->orWhere('sender_id', Auth::user()->id);
        })->where('status', 'Open')->first();

        if ($trade) {
            $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)
                ->get()
                ->filter(function ($userItem) {
                    return $userItem->isTransferrable == true;
                })
                ->sortBy('item.name');
        } else {
            $trade = null;
        }

        return view('home.trades.edit_trade', [
            'trade'               => $trade,
            'partner'             => (Auth::user()->id == $trade->sender_id) ? $trade->recipient : $trade->sender,
            'categories'          => ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'item_filter'         => Item::orderBy('name')->get()->keyBy('id'),
            'inventory'           => $inventory,
            'userOptions'         => User::visible()->orderBy('name')->pluck('name', 'id')->toArray(),
            'characters'          => Auth::user()->allCharacters()->visible()->with('designUpdate')->get(),
            'characterCategories' => CharacterCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'page'                => 'trade',
        ]);
    }

    /**
     * Creates a new trade.
     *
     * @param App\Services\TradeManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateTrade(Request $request, TradeManager $service) {
        if ($trade = $service->createTrade($request->only(['recipient_id', 'comments', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity', 'character_id']), Auth::user())) {
            flash('Trade created successfully.')->success();

            return redirect()->to($trade->url);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Edits a trade.
     *
     * @param App\Services\TradeManager $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditTrade(Request $request, TradeManager $service, $id) {
        if ($trade = $service->editTrade($request->only(['comments', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity', 'character_id']) + ['id' => $id], Auth::user())) {
            flash('Trade offer edited successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the offer confirmation modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getConfirmOffer($id) {
        $trade = Trade::where('id', $id)->where(function ($query) {
            $query->where('recipient_id', Auth::user()->id)->orWhere('sender_id', Auth::user()->id);
        })->where('status', 'Open')->first();

        return view('home.trades._confirm_offer_modal', [
            'trade' => $trade,
        ]);
    }

    /**
     * Confirms or unconfirms an offer.
     *
     * @param App\Services\TradeManager $service
     * @param mixed                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postConfirmOffer(Request $request, TradeManager $service, $id) {
        if ($trade = $service->confirmOffer(['id' => $id], Auth::user())) {
            flash('Trade offer confirmation edited successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the trade confirmation modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getConfirmTrade($id) {
        $trade = Trade::where('id', $id)->where(function ($query) {
            $query->where('recipient_id', Auth::user()->id)->orWhere('sender_id', Auth::user()->id);
        })->where('status', 'Open')->first();

        return view('home.trades._confirm_trade_modal', [
            'trade' => $trade,
        ]);
    }

    /**
     * Confirms or unconfirms a trade.
     *
     * @param App\Services\TradeManager $service
     * @param mixed                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postConfirmTrade(Request $request, TradeManager $service, $id) {
        if ($trade = $service->confirmTrade(['id' => $id], Auth::user())) {
            flash('Trade confirmed successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the trade cancellation modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCancelTrade($id) {
        $trade = Trade::where('id', $id)->where(function ($query) {
            $query->where('recipient_id', Auth::user()->id)->orWhere('sender_id', Auth::user()->id);
        })->where('status', 'Open')->first();

        return view('home.trades._cancel_trade_modal', [
            'trade' => $trade,
        ]);
    }

    /**
     * Cancels a trade.
     *
     * @param App\Services\TradeManager $service
     * @param mixed                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCancelTrade(Request $request, TradeManager $service, $id) {
        if ($trade = $service->cancelTrade(['id' => $id], Auth::user())) {
            flash('Trade canceled successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
