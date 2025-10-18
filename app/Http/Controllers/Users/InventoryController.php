<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Item\UserItemLog;
use App\Models\Character\Character;
use App\Models\Character\CharacterItem;
use App\Services\InventoryManager;

use App\Models\Trade;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Submission\Submission;
use App\Models\Queue\QueueSubmission;

use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Inventory Controller
    |--------------------------------------------------------------------------
    |
    | Handles inventory management for the user.
    |
    */

    /**
     * Shows the user's inventory page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $categories = ItemCategory::orderBy('sort', 'DESC')->get();
        $items = count($categories) ?
            Auth::user()->items()
                ->where('count', '>', 0)
                ->orderByRaw('FIELD(item_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')
                ->orderBy('name')
                ->orderBy('updated_at')
                ->get()
                ->groupBy(['item_category_id', 'id']) :
            Auth::user()->items()
                ->where('count', '>', 0)
                ->orderBy('name')
                ->orderBy('updated_at')
                ->get()
                ->groupBy(['item_category_id', 'id']);
        return view('home.inventory', [
            'categories' => $categories->keyBy('id'),
            'items' => $items,
            'userOptions' => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'user' => Auth::user()
        ]);
    }

    /**
     * Shows the inventory stack modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStack(Request $request, $id)
    {
        $first_instance = UserItem::withTrashed()->where('id', $id)->first();
        $readOnly = $request->get('read_only') ? : ((Auth::check() && $first_instance && ($first_instance->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories'))) ? 0 : 1);
        $stack = UserItem::where([['user_id', $first_instance->user_id], ['item_id', $first_instance->item_id], ['count', '>', 0]])->get();
        $item = Item::where('id', $first_instance->item_id)->first();

        return view('home._inventory_stack', [
            'stack' => $stack,
            'item' => $item,
            'user' => Auth::user(),
            'userOptions' => ['' => 'Select User'] + User::visible()->where('id', '!=', $first_instance ? $first_instance->user_id : 0)->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
            'readOnly' => $readOnly,
            'characterOptions' => Character::visible()->myo(0)->where('user_id', optional(Auth::user())->id)->orderBy('sort','DESC')->get()->pluck('fullName','id')->toArray(),
        ]);
    }

    /**
     * Shows the inventory stack modal, for characters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterStack(Request $request, $id)
    {
        $first_instance = CharacterItem::withTrashed()->where('id', $id)->first();
        $stack = CharacterItem::where([['character_id', $first_instance->character_id], ['item_id', $first_instance->item_id], ['count', '>', 0]])->get();
        $item = Item::where('id', $first_instance->item_id)->first();

        $character = $first_instance->character;
        isset($stack->first()->character->user_id) ?
        $ownerId = $stack->first()->character->user_id : null;

        $hasPower = Auth::check() ? Auth::user()->hasPower('edit_inventories') : false;
        $readOnly = $request->get('read_only') ? : ((Auth::check() && $first_instance && (isset($ownerId) == TRUE || $hasPower == TRUE)) ? 0 : 1);

        return view('character._inventory_stack', [
            'stack' => $stack,
            'item' => $item,
            'user' => Auth::user(),
            'has_power' => $hasPower,
            'readOnly' => $readOnly,
            'character' => $character,
            'owner_id' => isset($ownerId) ? $ownerId : null,
        ]);
    }

    /**
     * Edits the inventory of involved users.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\InventoryManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(Request $request, InventoryManager $service)
    {
        if(!$request->ids) { flash('No items selected.')->error(); }
        if(!$request->quantities) { flash('Quantities not set.')->error(); }

        if($request->ids && $request->quantities) {
            switch($request->action) {
                default:
                    flash('Invalid action selected.')->error();
                    break;
                case 'transfer':
                    return $this->postTransfer($request, $service);
                    break;
                case 'delete':
                    return $this->postDelete($request, $service);
                    break;
                case 'characterTransfer':
                    return $this->postTransferToCharacter($request, $service);
                    break;
                case 'resell':
                    return $this->postResell($request, $service);
                    break;
                case 'act':
                    return $this->postAct($request);
                    break;
            }
        }
        return redirect()->back();
    }

    /**
     * Transfers inventory items to another user.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\InventoryManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postTransfer(Request $request, InventoryManager $service)
    {
        if($service->transferStack(Auth::user(), User::visible()->where('id', $request->get('user_id'))->first(), UserItem::find($request->get('ids')), $request->get('quantities'))) {
            flash('Item transferred successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Transfers inventory items to another user.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\InventoryManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postTransferToCharacter(Request $request, InventoryManager $service)
    {
        if($service->transferCharacterStack(Auth::user(), Character::visible()->where('id', $request->get('character_id'))->first(), UserItem::find($request->get('ids')), $request->get('quantities'))) {
            flash('Item transferred successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Deletes an inventory stack.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\InventoryManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postDelete(Request $request, InventoryManager $service)
    {
        if($service->deleteStack(Auth::user(), UserItem::find($request->get('ids')), $request->get('quantities'))) {
            flash('Item deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Sells an inventory stack.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\InventoryManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postResell(Request $request, InventoryManager $service)
    {
        if($service->resellStack(Auth::user(), UserItem::find($request->get('ids')), $request->get('quantities'))) {
            flash('Item sold successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the inventory selection widget.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSelector($id)
    {
        return view('widgets._inventory_select', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Acts on an item based on the item's tag.
     *
     * @param  \Illuminate\Http\Request       $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postAct(Request $request)
    {
        $stacks = UserItem::with('item')->find($request->get('ids'));
        $tag = $request->get('tag');
        $service = $stacks->first()->item->hasTag($tag) ? $stacks->first()->item->tag($tag)->service : null;
        if($service && $service->act($stacks, Auth::user(), $request->all())) {
            flash('Item used successfully.')->success();
        }
        else if(!$stacks->first()->item->hasTag($tag)) flash('Invalid action selected.')->error();
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Show the account search page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAccountSearch(Request $request)
    {
        $item = Item::released()->find($request->only(['item_id']))->first();
        $user = Auth::user();

        if($item) {
            // Gather all instances of this item in the user's inventory
            $userItems = UserItem::where('user_id', $user->id)->where('item_id', $item->id)->where('count', '>', 0)->get();

            // Gather the user's characters and the items they own
            $characters = Character::where('user_id', $user->id)->orderBy('slug', 'ASC')->get();
            $characterItems = CharacterItem::whereIn('character_id', $characters->pluck('id')->toArray())->where('item_id', $item->id)->where('count', '>', 0)->get();

            // Gather hold locations
            $designUpdates = CharacterDesignUpdate::where('user_id', $user->id)->whereNotNull('data')->get();
            $trades = Trade::where('sender_id', $user->id)->orWhere('recipient_id', $user->id)->get();
            $submissions = Submission::where('user_id', $user->id)->whereNotNull('data')->get();
            $queuesubmissions = QueueSubmission::where('user_id', $user->id)->whereNotNull('data')->get();
        }

        return view('home.account_search', [
            'item' => $item ? $item : null,
            'items' => Item::orderBy('name')->released()->pluck('name', 'id'),
            'userItems' => $item ? $userItems : null,
            'characterItems' => $item ? $characterItems : null,
            'characters' => $item ? $characters : null,
            'designUpdates' => $item ? $designUpdates :null,
            'trades' => $item ? $trades : null,
            'submissions' => $item ? $submissions : null,
            'queuesubmissions' => $item ? $queuesubmissions : null,
        ]);
    }
}
