<?php

namespace App\Services;

use App\Models\Rank\Rank;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class RankService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Rank Service
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of user ranks.
    |
    */

    /**
     * Creates a user rank.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function createRank($data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Rank::where('name', $data['name'])->exists()) {
                throw new \Exception('A rank with the given name already exists.');
            }

            $powers = null;
            if (isset($data['powers'])) {
                foreach ($data['powers'] as $power) {
                    if (!config('lorekeeper.powers.'.$power)) {
                        throw new \Exception('Invalid power selected.');
                    }
                }

                $powers = array_unique($data['powers']);
                unset($data['powers']);
            }

            // Assign sort the sort value of the lowest rank + 1.
            // (This is because new users get assigned the lowest rank)
            // Ranks equal to and above the new rank also get + 1.
            $data['sort'] = Rank::orderBy('sort')->first()->sort + 1;
            Rank::where('sort', '>=', $data['sort'])->increment('sort');

            $data['color'] = isset($data['color']) ? str_replace('#', '', $data['color']) : null;
            if (isset($data['description']) && $data['description']) {
                $data['parsed_description'] = parse($data['description']);
            } else {
                $data['parsed_description'] = null;
            }

            $data['icon'] ??= 'fas fa-user';

            $rank = Rank::create($data);
            if ($powers) {
                foreach ($powers as $power) {
                    DB::table('rank_powers')->insert(['rank_id' => $rank->id, 'power' => $power]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a user rank.
     *
     * @param Rank  $rank
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function updateRank($rank, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Rank::where('name', $data['name'])->where('id', '!=', $rank->id)->exists()) {
                throw new \Exception('A rank with the given name already exists.');
            }

            $powers = null;
            if (isset($data['powers'])) {
                foreach ($data['powers'] as $power) {
                    if (!config('lorekeeper.powers.'.$power)) {
                        throw new \Exception('Invalid power selected.');
                    }
                }

                $powers = array_unique($data['powers']);
                unset($data['powers']);
            }

            $data['color'] = isset($data['color']) ? str_replace('#', '', $data['color']) : null;
            if (isset($data['description']) && $data['description']) {
                $data['parsed_description'] = parse($data['description']);
            } else {
                $data['parsed_description'] = null;
            }

            $data['icon'] ??= 'fas fa-user';

            $rank->update($data);
            $rank->powers()->delete();
            if ($powers) {
                foreach ($powers as $power) {
                    DB::table('rank_powers')->insert(['rank_id' => $rank->id, 'power' => $power]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a user rank.
     *
     * @param Rank $rank
     * @param User $user
     *
     * @return bool
     */
    public function deleteRank($rank, $user) {
        DB::beginTransaction();

        try {
            // Disallow deletion of ranks that are currently assigned to users
            if (User::where('rank_id', $rank->id)->exists()) {
                throw new \Exception('There are currently user(s) with the selected rank. Please change their rank before deleting this one.');
            }

            $rank->powers()->delete();
            $rank->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts user ranks.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function sortRanks($data, $user) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the power order is inverted
            $sort = array_reverse(explode(',', $data));

            // Check if the array contains the admin rank, or anything non-numeric
            $adminRank = Rank::orderBy('sort', 'DESC')->first();
            $count = 0;
            foreach ($sort as $key => $s) {
                if (!is_numeric($s) || !is_numeric($key)) {
                    throw new \Exception('Invalid sort order.');
                }
                if ($s == $adminRank->id) {
                    throw new \Exception('Sort order of admin rank cannot be changed.');
                }

                Rank::where('id', $s)->update(['sort' => $key]);
                $count++;
            }
            $adminRank->update(['sort'=> $count]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
