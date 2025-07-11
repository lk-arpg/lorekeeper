<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Character\CharacterCategory;
use Illuminate\Support\Facades\DB;

class CharacterCategoryService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Character Category Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of character categories.
    |
    */

    /**
     * Create a category.
     *
     * @param array $data
     * @param mixed $user
     *
     * @return bool|CharacterCategory
     */
    public function createCharacterCategory($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateCategoryData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $category = CharacterCategory::create($data);

            if (!$this->logAdminAction($user, 'Created Character Category', 'Created '.$category->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);
            }

            return $this->commitReturn($category);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param CharacterCategory $category
     * @param array             $data
     * @param mixed             $user
     *
     * @return bool|CharacterCategory
     */
    public function updateCharacterCategory($category, $data, $user) {
        DB::beginTransaction();

        try {
            if (CharacterCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            if (CharacterCategory::where('code', $data['code'])->where('id', '!=', $category->id)->exists()) {
                throw new \Exception('The code has already been taken.');
            }

            $data = $this->populateCategoryData($data, $category);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $category->update($data);

            if (!$this->logAdminAction($user, 'Edited Character Category', 'Edited '.$category->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($category) {
                $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);
            }

            return $this->commitReturn($category);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a category.
     *
     * @param CharacterCategory $category
     * @param mixed             $user
     *
     * @return bool
     */
    public function deleteCharacterCategory($category, $user) {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (Character::where('character_category_id', $category->id)->exists()) {
                throw new \Exception('An character with this category exists. Please change its category first.');
            }

            if (!$this->logAdminAction($user, 'Deleted Character Category', 'Deleted '.$category->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($category->has_image) {
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            }
            $category->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sortCharacterCategory($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                CharacterCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handle category data.
     *
     * @param array                  $data
     * @param CharacterCategory|null $category
     *
     * @return array
     */
    private function populateCategoryData($data, $category = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }

        if (isset($data['remove_image'])) {
            if ($category && $category->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
