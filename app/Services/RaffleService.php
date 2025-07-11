<?php

namespace App\Services;

use App\Models\Raffle\Raffle;
use App\Models\Raffle\RaffleGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RaffleService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Raffle Service
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of raffles.
    |
    */

    /**
     * Creates a raffle.
     *
     * @param array $data
     *
     * @return Raffle
     */
    public function createRaffle($data) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $raffle = Raffle::create(Arr::only($data, ['name', 'is_active', 'winner_count', 'group_id', 'order']));
        DB::commit();

        return $raffle;
    }

    /**
     * Updates a raffle.
     *
     * @param array  $data
     * @param Raffle $raffle
     *
     * @return Raffle
     */
    public function updateRaffle($data, $raffle) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $raffle->update(Arr::only($data, ['name', 'is_active', 'winner_count', 'group_id', 'order', 'ticket_cap']));
        DB::commit();

        return $raffle;
    }

    /**
     * Deletes a raffle.
     *
     * @param Raffle $raffle
     *
     * @return bool
     */
    public function deleteRaffle($raffle) {
        DB::beginTransaction();
        foreach ($raffle->tickets as $ticket) {
            $ticket->delete();
        }
        $raffle->delete();
        DB::commit();

        return true;
    }

    /**
     * Creates a raffle group.
     *
     * @param array $data
     *
     * @return RaffleGroup
     */
    public function createRaffleGroup($data) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $group = RaffleGroup::create(Arr::only($data, ['name', 'is_active']));
        DB::commit();

        return $group;
    }

    /**
     * Updates a raffle group.
     *
     * @param array $data
     * @param mixed $group
     *
     * @return Raffle
     */
    public function updateRaffleGroup($data, $group) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $group->update(Arr::only($data, ['name', 'is_active']));
        foreach ($group->raffles as $raffle) {
            $raffle->update(['is_active' => $data['is_active']]);
        }
        DB::commit();

        return $group;
    }

    /**
     * Deletes a raffle group.
     *
     * @param mixed $group
     *
     * @return bool
     */
    public function deleteRaffleGroup($group) {
        DB::beginTransaction();
        foreach ($group->raffles as $raffle) {
            $raffle->update(['group_id' => null]);
        }
        $group->delete();
        DB::commit();

        return true;
    }
}
