<?php

namespace App\Services;

use App\Models\Character\CharacterImage;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use Illuminate\Support\Facades\DB;

class SpeciesService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Species Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of character species.
    |
    */

    /**
     * Creates a new species.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Species
     */
    public function createSpecies($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $species = Species::create($data);

            if ($image) {
                $this->handleImage($image, $species->speciesImagePath, $species->speciesImageFileName);
            }

            return $this->commitReturn($species);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a species.
     *
     * @param Species               $species
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Species
     */
    public function updateSpecies($species, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Species::where('name', $data['name'])->where('id', '!=', $species->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateData($data, $species);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $species->update($data);

            if ($species) {
                $this->handleImage($image, $species->speciesImagePath, $species->speciesImageFileName);
            }

            return $this->commitReturn($species);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a species.
     *
     * @param Species $species
     *
     * @return bool
     */
    public function deleteSpecies($species) {
        DB::beginTransaction();

        try {
            // Check first if characters with this species exists
            if (CharacterImage::where('species_id', $species->id)->exists()) {
                throw new \Exception('A character image with this species exists. Please change its species first.');
            }

            if ($species->has_image) {
                $this->deleteImage($species->speciesImagePath, $species->speciesImageFileName);
            }
            $species->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts species order.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sortSpecies($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Species::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a new subtype.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Subtype
     */
    public function createSubtype($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateSubtypeData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $subtype = Subtype::create($data);

            if ($image) {
                $this->handleImage($image, $subtype->subtypeImagePath, $subtype->subtypeImageFileName);
            }

            return $this->commitReturn($subtype);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a subtype.
     *
     * @param Subtype               $subtype
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Subtype
     */
    public function updateSubtype($subtype, $data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateSubtypeData($data, $subtype);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $subtype->update($data);

            if ($subtype) {
                $this->handleImage($image, $subtype->subtypeImagePath, $subtype->subtypeImageFileName);
            }

            return $this->commitReturn($subtype);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a subtype.
     *
     * @param Subtype $subtype
     *
     * @return bool
     */
    public function deleteSubtype($subtype) {
        DB::beginTransaction();

        try {
            // Check first if characters with this subtype exists
            if (CharacterImage::where('subtype_id', $subtype->id)->exists()) {
                throw new \Exception('A character image with this subtype exists. Please change or remove its subtype first.');
            }

            if ($subtype->has_image) {
                $this->deleteImage($subtype->subtypeImagePath, $subtype->subtypeImageFileName);
            }
            $subtype->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts subtype order.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sortSubtypes($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Subtype::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a species.
     *
     * @param array   $data
     * @param Species $species
     *
     * @return array
     */
    private function populateData($data, $species = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (isset($data['remove_image'])) {
            if ($species && $species->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($species->speciesImagePath, $species->speciesImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Processes user input for creating/updating a subtype.
     *
     * @param array   $data
     * @param Subtype $subtype
     *
     * @return array
     */
    private function populateSubtypeData($data, $subtype = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (isset($data['remove_image'])) {
            if ($subtype && $subtype->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($subtype->subtypeImagePath, $subtype->subtypeImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
