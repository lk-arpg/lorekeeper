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

        try {
            $data = $this->populateData($data);
            $raffle = Raffle::create(Arr::only($data, [
                'name', 'is_active', 'winner_count', 'group_id', 'order', 'allow_entry', 'is_fto', 'unordered', 'ticket_cap', 'end_at', 'roll_on_end',
                'description', 'parsed_description',
            ]));
            if (!$this->populateRewards($data, $raffle)) {
                throw new \Exception('Failed to create rewards.');
            }

            return $this->commitReturn($raffle);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
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

        try {
            $data = $this->populateData($data, $raffle);
            $raffle->update(Arr::only($data, [
                'name', 'is_active', 'winner_count', 'group_id', 'order', 'allow_entry', 'is_fto', 'unordered', 'ticket_cap', 'end_at', 'roll_on_end',
                'description', 'parsed_description',
            ]));
            if (!$this->populateRewards($data, $raffle)) {
                throw new \Exception('Failed to update rewards.');
            }

            return $this->commitReturn($raffle);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
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

        try {
            $raffle->tickets()->delete();
            $raffle->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
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

        try {
            if (!isset($data['is_active'])) {
                $data['is_active'] = 0;
            }
            $group = RaffleGroup::create(Arr::only($data, ['name', 'is_active']));

            return $this->commitReturn($group);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
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

        try {
            if (!isset($data['is_active'])) {
                $data['is_active'] = 0;
            }
            $group->update(Arr::only($data, ['name', 'is_active']));
            $group->raffles()->update(['is_active' => $data['is_active']]);

            return $this->commitReturn($group);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
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

        try {
            $group->raffles()->update(['group_id' => null]);
            $group->delete();

            $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /*
     * Processes user input data for creating/updating raffles.
     *
     * @param array  $data
     * @param Raffle $raffle
     *
     * @return array
     */
    private function populateData($data, $raffle = null) {
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        if (!isset($data['allow_entry'])) {
            $data['allow_entry'] = 0;
        }
        if (!isset($data['is_fto'])) {
            $data['is_fto'] = 0;
        }
        if (!isset($data['unordered'])) {
            $data['unordered'] = 0;
        }
        if (!isset($data['roll_on_end']) || !isset($data['end_at'])) {
            $data['roll_on_end'] = 0;
        }
        if (isset($data['description'])) {
            $data['parsed_description'] = parse($data['description']);
        } else {
            $data['parsed_description'] = null;
        }

        return $data;
    }

    /*
     * Processes user input for creating/updating raffle rewards.
     *
     * @param array  $data
     * @param Raffle $raffle
     *
     * @return void
     */
    private function populateRewards($data, $raffle) {
        $rows = [];
        $rewardService = new RewardService;
        if (isset($data['entry_rewardable_type'])) {
            foreach ($data['entry_rewardable_type'] as $key => $rewardableType) {
                $rows[] = [
                    'rewardable_type'      => $rewardableType,
                    'rewardable_id'        => $data['entry_rewardable_id'][$key],
                    'quantity'             => $data['entry_quantity'][$key],
                    'type'                 => 'entry_reward',
                    // Entry rewards don't have positions,
                    // but we need to set it to something for array_column to work correctly in the rewardData array, so we'll just set it to 1 for all entry rewards.
                    'position'             => 1,
                ];
            }
        }
        if (isset($data['winner_rewardable_type'])) {
            foreach ($data['winner_rewardable_type'] as $key => $rewardableType) {
                $rows[] = [
                    'rewardable_type'      => $rewardableType,
                    'rewardable_id'        => $data['winner_rewardable_id'][$key],
                    'quantity'             => $data['winner_quantity'][$key],
                    'type'                 => 'winner_reward',
                    'position'             => $data['winner_position'][$key],
                ];
            }
        }
        $rewardData = [
            'rewardable_type' => array_column($rows, 'rewardable_type'),
            'rewardable_id'   => array_column($rows, 'rewardable_id'),
            'quantity'        => array_column($rows, 'quantity'),
            'data'            => [
                'type'     => array_column($rows, 'type'),
                'position' => array_column($rows, 'position'),
            ],
        ];

        // check if we have a character reward and if so, validate that:
        // 1. the character exists
        // 2. it is NOT an entry reward (characters cannot be entry rewards)
        // 3. it MUST have a winner position (characters cannot be winner rewards without a position, otherwise we wouldn't know which winner gets the character)
        if (in_array('Character', $rewardData['rewardable_type'])) {
            $characterRewardKeys = array_keys($rewardData['rewardable_type'], 'Character');
            foreach ($characterRewardKeys as $key) {
                if ($rewardData['data']['type'][$key] == 'entry_reward') {
                    throw new \Exception('Character rewards cannot be entry rewards.');
                }
                if (!isset($rewardData['data']['position'][$key]) || !$rewardData['data']['position'][$key]) {
                    throw new \Exception('Character rewards must have a winner position specified.');
                }
                $character = \App\Models\Character\Character::find($rewardData['rewardable_id'][$key]);
                if (!$character || $character->is_myo) {
                    throw new \Exception('Character reward with ID '.$rewardData['rewardable_id'][$key].' does not exist.');
                }
            }
        }

        if (!$rewardService->populateRewards(
            get_class($raffle),
            $raffle->id,
            $rewardData,
            true
        )) {
            foreach ($rewardService->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
            throw new \Exception('Failed to create rewdddards.');
        }

        return true;
    }
}
