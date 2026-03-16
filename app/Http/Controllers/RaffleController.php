<?php

namespace App\Http\Controllers;

use App\Models\Raffle\Raffle;
use App\Services\RaffleManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class RaffleController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Raffle Controller
    |--------------------------------------------------------------------------
    |
    | Displays raffles and raffle tickets.
    |
    */

    /**
     * Shows the raffle index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaffleIndex() {
        $raffles = Raffle::query();
        if (Request::get('view') == 'completed') {
            $raffles->where('is_active', 2);
        } else {
            $raffles->where('is_active', '=', 1);
        }
        $query = $raffles->orderBy('group_id')
            ->orderBy('order');

        if (!Auth::check() || Auth::check() && !Auth::user()->hasPower('manage_raffles')) {
            $query = $query->get()->filter(function ($q) {
                if ($q->group) {
                    return $q->group->is_active;
                }

                return $q->is_active;
            });
        } else {
            $query = $query->get();
        }

        $grouped = $query->groupBy(function ($item) {
            return $item->group ? $item->group->name : 'Ungrouped';
        });

        return view('raffles.index', [
            'raffles' => $grouped,
        ]);
    }

    /**
     * Shows tickets for a given raffle.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaffleTickets($id) {
        $raffle = Raffle::find($id);
        if (!$raffle || !$raffle->is_active || ($raffle->group && !$raffle->group->is_active)) {
            abort(404);
        }
        $userCount = Auth::check() ? $raffle->tickets()->where('user_id', Auth::user()->id)->count() : 0;
        $count = $raffle->tickets()->count();

        return view('raffles.ticket_index', [
            'raffle'    => $raffle,
            'tickets'   => $raffle->tickets()->with('user')->orderBy('id')->paginate(100),
            'count'     => $count,
            'userCount' => $userCount,
            'page'      => Request::get('page') ? Request::get('page') - 1 : 0,
        ]);
    }

    // allows self entry
    public function selfEnter($id, RaffleManager $service) {
        $raffle = Raffle::find($id);
        if (!$raffle || !$raffle->is_active) {
            abort(404);
        }
        $user = Auth::user();
        if (!$user) {
            abort(404);
        }

        if ($service->selfEnter($raffle, $user)) {
            flash('Entered successfully!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
