<?php

namespace App\Services;

use App\Models\Character\CharacterCategory;
use App\Models\Character\Sublist;
use App\Models\Species\Species;
use Illuminate\Support\Facades\DB;

class SublistService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Sub Masterlist Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of sub masterlists.
    |
    */

    /**********************************************************************************************

        SUB MASTERLISTS

    **********************************************************************************************/

    /**
     * Create a sublist.
     *
     * @param array $data
     * @param array $contents
     *
     * @return bool|Sublist
     */
    public function createSublist($data, $contents) {
        DB::beginTransaction();

        try {
            $sublist = Sublist::create($data);

            // update categories and species
            if (isset($contents['categories']) && $contents['categories']) {
                CharacterCategory::whereIn('id', $contents['categories'])->update(['masterlist_sub_id' => $sublist->id]);
            }
            if (isset($contents['species']) && $contents['species']) {
                Species::whereIn('id', $contents['species'])->update(['masterlist_sub_id' => $sublist->id]);
            }

            return $this->commitReturn($sublist);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a sublist.
     *
     * @param Sublist $sublist
     * @param array   $data
     * @param array   $contents
     *
     * @return bool|Sublist
     */
    public function updateSublist($sublist, $data, $contents) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Sublist::where('name', $data['name'])->where('id', '!=', $sublist->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            // update sublist
            $sublist->update($data);

            // update categories and species
            CharacterCategory::where('masterlist_sub_id', $sublist->id)->update(['masterlist_sub_id' => 0]);
            Species::where('masterlist_sub_id', $sublist->id)->update(['masterlist_sub_id' => 0]);
            if (isset($contents['categories'])) {
                CharacterCategory::whereIn('id', $contents['categories'])->update(['masterlist_sub_id' => $sublist->id]);
            }
            if (isset($contents['species'])) {
                Species::whereIn('id', $contents['species'])->update(['masterlist_sub_id' => $sublist->id]);
            }

            return $this->commitReturn($sublist);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a sublist.
     *
     * @param Sublist $sublist
     *
     * @return bool
     */
    public function deleteSublist($sublist) {
        DB::beginTransaction();

        try {
            // Check first if the sublist is currently in use
            CharacterCategory::where('masterlist_sub_id', $sublist->id)->update(['masterlist_sub_id' => 0]);
            Species::where('masterlist_sub_id', $sublist->id)->update(['masterlist_sub_id' => 0]);

            $sublist->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts sublist  order.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sortSublist($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Sublist::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
