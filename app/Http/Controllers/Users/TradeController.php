<?php

namespace App\Http\Controllers\Users;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Character\CharacterCategory;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Trade\Trade;
use App\Models\Trade\TradeListing;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\TradeListingManager;
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
        $trades = Trade::with('recipient')->with('sender')->with('staff')->where(function ($query) {
            $query->where('recipient_id', Auth::user()->id)->orWhere('sender_id', Auth::user()->id);
        })->where('status', $status == 'proposals' ? 'Proposal' : ucfirst($status))->orderBy('id', 'DESC');

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
        $item_filter = Item::orderBy('name')->get()->mapWithKeys(function ($item) {
            return [
                $item->id => json_encode([
                    'name'      => $item->name,
                    'image_url' => $item->image_url,
                ]),
            ];
        });

        return view('home.trades.create_trade', [
            'categories'          => ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'item_filter'         => $item_filter,
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
        })->whereIn('status', ['Open', 'Proposal'])->first();

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

        $item_filter = Item::orderBy('name')->get()->mapWithKeys(function ($item) {
            return [
                $item->id => json_encode([
                    'name'      => $item->name,
                    'image_url' => $item->image_url,
                ]),
            ];
        });

        return view('home.trades.edit_trade', [
            'trade'               => $trade,
            'partner'             => (Auth::user()->id == $trade->sender_id) ? $trade->recipient : $trade->sender,
            'categories'          => ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'item_filter'         => $item_filter,
            'inventory'           => $inventory,
            'userOptions'         => User::visible()->orderBy('name')->pluck('name', 'id')->toArray(),
            'characters'          => Auth::user()->allCharacters()->visible()->with('designUpdate')->get(),
            'characterCategories' => CharacterCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'page'                => 'trade',
        ]);
    }

    /**
     * Gets the propose trade page.
     *
     * @param mixed|null $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEditTradeProposal(Request $request, $id = null) {
        $trade = Trade::where('id', $id)->where('status', 'Proposal')->first();
        $recipient = $trade ? (Auth::user()->id == $trade->recipient->id ? $trade->sender : $trade->recipient)
            : User::find($request->input('recipient_id'));
        $tradeListing = $request->input('trade_listing_id') ? TradeListing::find($request->input('trade_listing_id')) : null;
        if ($recipient) {
            $recipientInventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', $recipient->id)
                ->get()
                ->filter(function ($userItem) {
                    return $userItem->isTransferrable == true;
                })
                ->sortBy('item.name');
            if ($tradeListing) {
                $recipientSelectedItems = $tradeListing->data['offering']['user_items'] ?? [];
                $recipientSelectedCharacters = array_keys($tradeListing->data['offering']['characters'] ?? []);
            }
        }

        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)
            ->get()
            ->filter(function ($userItem) {
                return $userItem->isTransferrable == true;
            })
            ->sortBy('item.name');
        $item_filter = Item::orderBy('name')->get()->mapWithKeys(function ($item) {
            return [
                $item->id => json_encode([
                    'name'      => $item->name,
                    'image_url' => $item->image_url,
                ]),
            ];
        });

        return view('home.trades.create_edit_trade_proposal', [
            'trade'                       => $id ? Trade::where('id', $id)->where('status', 'Proposal')->first() : null,
            'recipient'                   => $recipient ?? null,
            'recipientInventory'          => $recipientInventory ?? null,
            'recipientSelectedItems'      => $recipientSelectedItems ?? [],
            'recipientSelectedCharacters' => $recipientSelectedCharacters ?? [],
            'categories'                  => ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'item_filter'                 => $item_filter,
            'inventory'                   => $inventory,
            'userOptions'                 => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'characterCategories'         => CharacterCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'page'                        => 'proposal',
        ]);
    }

    /**
     * Returns the mini view for the trade proposal for the recipient.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserTradeProposal($id) {
        $user = User::findOrFail($id);
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', $user->id)->get()->filter(function ($userItem) {
            return $userItem->isTransferrable == true;
        })->sortBy('item.name');

        $item_filter = Item::orderBy('name')->get()->mapWithKeys(function ($item) {
            return [
                $item->id => json_encode([
                    'name'      => $item->name,
                    'image_url' => $item->image_url,
                ]),
            ];
        });

        return view('home.trades._proposal_offer', [
            'user'                => $user,
            'inventory'           => $inventory,
            'item_filter'         => $item_filter,
            'categories'          => ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'page'                => 'proposal',
            'characters'          => $user->allCharacters()->visible()->tradable()->with('designUpdate')->get(),
            'characterCategories' => CharacterCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'fieldPrefix'         => 'recipient_',
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
        if ($trade = $service->createTrade($request->only([
            'recipient_id', 'comments', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity', 'character_id', 'terms_link',
        ]), Auth::user())) {
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
        if ($trade = $service->editTrade($request->only([
            'comments', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity', 'character_id',
        ]) + ['id' => $id], Auth::user())) {
            flash('Trade offer edited successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Proposes a trade.
     *
     * @param App\Services\TradeManager $service
     * @param mixed|null                $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditTradeProposal(Request $request, TradeManager $service, $id = null) {
        $existingTrade = $id ? Trade::where('id', $id)->where(function ($query) {
            $query->where('recipient_id', Auth::user()->id)->orWhere('sender_id', Auth::user()->id);
        })->where('status', 'Proposal')->first() : null;
        if ($trade = $service->proposeTrade($request->only([
            'recipient_id', 'comments', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity', 'character_id',
            'recipient_stack_id', 'recipient_stack_quantity', 'recipient_character_id',
        ]), Auth::user(), $existingTrade)) {
            flash('Trade '.($existingTrade ? 'proposal edited' : 'proposed').' successfully.')->success();

            return redirect()->to($trade->url);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Rejects or accepts a trade proposal.
     *
     * @param App\Services\TradeManager $service
     * @param mixed                     $id
     * @param mixed                     $action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRespondToTradeProposal(Request $request, TradeManager $service, $id, $action) {
        $trade = Trade::where('id', $id)->where(function ($query) {
            $query->where('recipient_id', Auth::user()->id)->orWhere('sender_id', Auth::user()->id);
        })->where('status', 'Proposal')->first();
        if (!$service->respondToTradeProposal($trade, Auth::user(), $action)) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('Trade proposal '.$action.'ed successfully.')->success();
        }

        return redirect()->to($trade->url);
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

    /**********************************************************************************************

        TRADE LISTINGS

    **********************************************************************************************/

    /**
     * Shows the trade listing index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getListingIndex(Request $request) {
        return view('home.trades.listings.index', [
            'listings'        => TradeListing::active()->orderBy('id', 'DESC')->paginate(10),
            'listingDuration' => Settings::get('trade_listing_duration'),
        ]);
    }

    /**
     * Shows the user's expired trade listings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getExpiredListings(Request $request) {
        return view('home.trades.listings.listings', [
            'listings'        => TradeListing::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->paginate(10),
            'listingDuration' => Settings::get('trade_listing_duration'),
        ]);
    }

    /**
     * Shows a trade.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getListing($id) {
        $listing = TradeListing::find($id);
        if (!$listing) {
            abort(404);
        }

        return view('home.trades.listings.view_listing', [
            'listing'      => $listing,
            'seekingData'  => isset($listing->data['seeking']) ? parseAssetData($listing->data['seeking']) : null,
            'offeringData' => isset($listing->data['offering']) ? parseAssetData($listing->data['offering']) : null,
            'items'        => Item::all()->keyBy('id'),
        ]);
    }

    /**
     * Shows the create trade listing page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateListing(Request $request) {
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)
            ->get()
            ->filter(function ($userItem) {
                return $userItem->isTransferrable == true;
            })
            ->sortBy('item.name');
        $currencies = Currency::where('is_user_owned', 1)->where('allow_user_to_user', 1)->orderBy('sort_user', 'DESC')->get()->pluck('name', 'id');
        $item_filter = Item::orderBy('name')->get()->mapWithKeys(function ($item) {
            return [
                $item->id => json_encode([
                    'name'           => $item->name,
                    'image_url'      => $item->image_url,
                ]),
            ];
        });

        return view('home.trades.listings.create_edit_listing', [
            'listing'             => new TradeListing,
            'currencies'          => $currencies,
            'categories'          => ItemCategory::orderBy('sort', 'DESC')->get(),
            'item_filter'         => $item_filter,
            'inventory'           => $inventory,
            'characters'          => Auth::user()->allCharacters()->visible()->tradable()->with('designUpdate')->get(),
            'characterCategories' => CharacterCategory::orderBy('sort', 'DESC')->get(),
            'page'                => 'listing',
            'listingDuration'     => Settings::get('trade_listing_duration'),
        ]);
    }

    /**
     * Shows the edit trade listing page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditListing($id) {
        $listing = TradeListing::find($id);
        if (!$listing) {
            abort(404);
        }

        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)
            ->get()
            ->filter(function ($userItem) {
                return $userItem->isTransferrable == true;
            })
            ->sortBy('item.name');
        $currencies = Currency::where('is_user_owned', 1)->where('allow_user_to_user', 1)->orderBy('sort_user', 'DESC')->get()->pluck('name', 'id');
        $item_filter = Item::orderBy('name')->get()->mapWithKeys(function ($item) {
            return [
                $item->id => json_encode([
                    'name'           => $item->name,
                    'image_url'      => $item->image_url,
                ]),
            ];
        });

        return view('home.trades.listings.create_edit_listing', [
            'listing'             => $listing,
            'currencies'          => $currencies,
            'categories'          => ItemCategory::orderBy('sort', 'DESC')->get(),
            'item_filter'         => $item_filter,
            'inventory'           => $inventory,
            'characters'          => Auth::user()->allCharacters()->visible()->tradable()->with('designUpdate')->get(),
            'characterCategories' => CharacterCategory::orderBy('sort', 'DESC')->get(),
            'page'                => 'listing',
            'listingDuration'     => Settings::get('trade_listing_duration'),
        ]);
    }

    /**
     * Creates a new trade listing.
     *
     * @param App\Services\TradeListingManager $service
     * @param mixed|null                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditListing(Request $request, TradeListingManager $service, $id = null) {
        if (!$listing = $service->createEditTradeListing($request->only([
            'title', 'comments', 'contact', 'item_ids', 'offering_etc', 'seeking_etc',
            'rewardable_type', 'rewardable_id', 'quantity',
            'offer_currency_ids', 'character_id', 'stack_id', 'stack_quantity',
        ]), Auth::user(), $id)) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }

        flash('Trade listing '.($id ? 'edited' : 'created').' successfully.')->success();

        return redirect()->to($listing->url);
    }

    /**
     * Manually marks a trade listing as expired.
     *
     * @param App\Services\TradeListingManager $service
     * @param int                              $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postExpireListing(Request $request, TradeListingManager $service, $id) {
        $listing = TradeListing::find($id);
        if (!$listing) {
            abort(404);
        }

        if ($service->markExpired(['id' => $id], Auth::user())) {
            flash('Listing expired successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
