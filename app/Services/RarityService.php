<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use App\Models\Rarity;
use Illuminate\Support\Facades\DB;

class RarityService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Rarity Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of rarities.
    |
    */

    /**
     * Creates a new rarity.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Rarity
     */
    public function createRarity($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['hash'] = randomString(10);
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $rarity = Rarity::create($data);

            if ($image) {
                $this->handleImage($image, $rarity->rarityImagePath, $rarity->rarityImageFileName);
            }

            return $this->commitReturn($rarity);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a rarity.
     *
     * @param Rarity                $rarity
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Rarity
     */
    public function updateRarity($rarity, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Rarity::where('name', $data['name'])->where('id', '!=', $rarity->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateData($data, $rarity);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $rarity->update($data);

            if ($rarity) {
                $this->handleImage($image, $rarity->rarityImagePath, $rarity->rarityImageFileName);
            }

            return $this->commitReturn($rarity);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a rarity.
     *
     * @param Rarity $rarity
     *
     * @return bool
     */
    public function deleteRarity($rarity) {
        DB::beginTransaction();

        try {
            // Check first if characters with this rarity exist
            if (CharacterImage::where('rarity_id', $rarity->id)->exists() || Character::where('rarity_id', $rarity->id)->exists()) {
                throw new \Exception('A character or character image with this rarity exists. Please change its rarity first.');
            }

            if ($rarity->has_image) {
                $this->deleteImage($rarity->rarityImagePath, $rarity->rarityImageFileName);
            }
            $rarity->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts rarity order.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sortRarity($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Rarity::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a rarity.
     *
     * @param array  $data
     * @param Rarity $rarity
     *
     * @return array
     */
    private function populateData($data, $rarity = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (isset($data['color'])) {
            $data['color'] = str_replace('#', '', $data['color']);
        }

        if (isset($data['remove_image'])) {
            if ($rarity && $rarity->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($rarity->rarityImagePath, $rarity->rarityImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
