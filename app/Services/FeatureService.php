<?php

namespace App\Services;

use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use Illuminate\Support\Facades\DB;

class FeatureService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Feature Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of feature categories and features.
    |
    */

    /**********************************************************************************************

        FEATURE CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|FeatureCategory
     */
    public function createFeatureCategory($data, $user) {
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

            $category = FeatureCategory::create($data);

            if (!$this->logAdminAction($user, 'Created Feature Category', 'Created '.$category->displayName)) {
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
     * @param FeatureCategory       $category
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|FeatureCategory
     */
    public function updateFeatureCategory($category, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (FeatureCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) {
                throw new \Exception('The name has already been taken.');
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

            if (!$this->logAdminAction($user, 'Updated Feature Category', 'Updated '.$category->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if (!$this->logAdminAction($user, 'Updated Feature Category', 'Updated '.$category->displayName)) {
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
     * @param FeatureCategory $category
     * @param mixed           $user
     *
     * @return bool
     */
    public function deleteFeatureCategory($category, $user) {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (Feature::where('feature_category_id', $category->id)->exists()) {
                throw new \Exception('A trait with this category exists. Please change its category first.');
            }

            if (!$this->logAdminAction($user, 'Deleted Feature Category', 'Deleted '.$category->name)) {
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
    public function sortFeatureCategory($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                FeatureCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        FEATURES

    **********************************************************************************************/

    /**
     * Creates a new feature.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Feature
     */
    public function createFeature($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['feature_category_id']) && $data['feature_category_id'] == 'none') {
                $data['feature_category_id'] = null;
            }
            if (isset($data['species_id']) && $data['species_id'] == 'none') {
                $data['species_id'] = null;
            }
            if (isset($data['subtype_id']) && $data['subtype_id'] == 'none') {
                $data['subtype_id'] = null;
            }

            if ((isset($data['feature_category_id']) && $data['feature_category_id']) && !FeatureCategory::where('id', $data['feature_category_id'])->exists()) {
                throw new \Exception('The selected trait category is invalid.');
            }
            if ((isset($data['species_id']) && $data['species_id']) && !Species::where('id', $data['species_id'])->exists()) {
                throw new \Exception('The selected species is invalid.');
            }
            if (isset($data['subtype_id']) && $data['subtype_id']) {
                $subtype = Subtype::find($data['subtype_id']);
                if (!(isset($data['species_id']) && $data['species_id'])) {
                    throw new \Exception('Species must be selected to select a subtype.');
                }
                if (!$subtype || $subtype->species_id != $data['species_id']) {
                    throw new \Exception('Selected subtype invalid or does not match species.');
                }
            }

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

            $feature = Feature::create($data);

            if (!$this->logAdminAction($user, 'Created Feature', 'Created '.$feature->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $feature->imagePath, $feature->imageFileName);
            }

            return $this->commitReturn($feature);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a feature.
     *
     * @param Feature               $feature
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Feature
     */
    public function updateFeature($feature, $data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['feature_category_id']) && $data['feature_category_id'] == 'none') {
                $data['feature_category_id'] = null;
            }
            if (isset($data['species_id']) && $data['species_id'] == 'none') {
                $data['species_id'] = null;
            }
            if (isset($data['subtype_id']) && $data['subtype_id'] == 'none') {
                $data['subtype_id'] = null;
            }

            // More specific validation
            if (Feature::where('name', $data['name'])->where('id', '!=', $feature->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            if ((isset($data['feature_category_id']) && $data['feature_category_id']) && !FeatureCategory::where('id', $data['feature_category_id'])->exists()) {
                throw new \Exception('The selected trait category is invalid.');
            }
            if ((isset($data['species_id']) && $data['species_id']) && !Species::where('id', $data['species_id'])->exists()) {
                throw new \Exception('The selected species is invalid.');
            }
            if (isset($data['subtype_id']) && $data['subtype_id']) {
                $subtype = Subtype::find($data['subtype_id']);
                if (!(isset($data['species_id']) && $data['species_id'])) {
                    throw new \Exception('Species must be selected to select a subtype.');
                }
                if (!$subtype || $subtype->species_id != $data['species_id']) {
                    throw new \Exception('Selected subtype invalid or does not match species.');
                }
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $feature->update($data);

            if (!$this->logAdminAction($user, 'Updated Feature', 'Updated '.$feature->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($feature) {
                $this->handleImage($image, $feature->imagePath, $feature->imageFileName);
            }

            return $this->commitReturn($feature);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a feature.
     *
     * @param Feature $feature
     * @param mixed   $user
     *
     * @return bool
     */
    public function deleteFeature($feature, $user) {
        DB::beginTransaction();

        try {
            // Check first if the feature is currently in use
            if (DB::table('character_features')->where('feature_id', $feature->id)->exists()) {
                throw new \Exception('A character with this trait exists. Please remove the trait first.');
            }

            if (!$this->logAdminAction($user, 'Deleted Feature', 'Deleted '.$feature->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($feature->has_image) {
                $this->deleteImage($feature->imagePath, $feature->imageFileName);
            }
            $feature->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handle category data.
     *
     * @param array                $data
     * @param FeatureCategory|null $category
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

    /**
     * Processes user input for creating/updating a feature.
     *
     * @param array   $data
     * @param Feature $feature
     *
     * @return array
     */
    private function populateData($data, $feature = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }
        if (isset($data['species_id']) && $data['species_id'] == 'none') {
            $data['species_id'] = null;
        }
        if (isset($data['feature_category_id']) && $data['feature_category_id'] == 'none') {
            $data['feature_category_id'] = null;
        }
        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (isset($data['remove_image'])) {
            if ($feature && $feature->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($feature->imagePath, $feature->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
