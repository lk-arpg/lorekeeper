<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Trade\Trade;
use App\Services\TradeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradeController extends Controller {
    /**
     * Shows the character trade queue.
     *
     * @param string $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTradeQueue(Request $request, $type) {
        $trades = Trade::query();
        $user = Auth::user();
        $data = $request->only(['sort']);
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $trades->sortNewest();
                    break;
                case 'oldest':
                    $trades->sortOldest();
                    break;
            }
        } else {
            $trades->sortOldest();
        }

        if ($type == 'completed') {
            $trades->completed();
        } elseif ($type == 'incoming') {
            $trades->where('status', 'Pending');
        } else {
            abort(404);
        }

        $openTransfersQueue = Settings::get('open_trades_queue') ?? Settings::get('open_transfers_queue');

        return view('admin.trades.trades', [
            'trades'             => $trades->orderBy('id', 'DESC')->paginate(20),
            'tradesQueue'        => Settings::get('open_trades_queue'),
            'tradeCount'         => $openTransfersQueue ? Trade::where('status', 'Pending')->count() : 0,
        ]);
    }

    /**
     * Shows the character trade action modal.
     *
     * @param int    $id
     * @param string $action
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTradeModal($id, $action) {
        if ($action != 'approve' && $action != 'reject') {
            abort(404);
        }
        $trade = Trade::where('id', $id)->first();
        if (!$trade) {
            abort(404);
        }

        return view('admin.trades._'.$action.'_trade_modal', [
            'trade'    => $trade,
            'cooldown' => Settings::get('transfer_cooldown'),
        ]);
    }

    /**
     * Acts on a trade in the trade queue.
     *
     * @param App\Services\CharacterManager $service
     * @param int                           $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTradeQueue(Request $request, TradeManager $service, $id) {
        if (!Auth::check()) {
            abort(404);
        }

        $action = strtolower($request->get('action'));
        if ($action == 'approve' && $service->approveTrade($request->only(['action', 'cooldowns']) + ['id' => $id], Auth::user())) {
            flash('Trade approved.')->success();
        } elseif ($action == 'reject' && $service->rejectTrade($request->only(['action', 'reason']) + ['id' => $id], Auth::user())) {
            flash('Trade rejected.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
