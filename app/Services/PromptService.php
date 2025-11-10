<?php

namespace App\Services;

use App\Models\Prompt\Prompt;
use App\Models\Prompt\PromptCategory;
use App\Models\Prompt\PromptReward;
use App\Models\Submission\Submission;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PromptService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Prompt Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of prompt categories and prompts.
    |
    */

    /**********************************************************************************************

        PROMPT CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PromptCategory
     */
    public function createPromptCategory($data, $user) {
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

            $category = PromptCategory::create($data);

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
     * @param PromptCategory        $category
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PromptCategory
     */
    public function updatePromptCategory($category, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (PromptCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) {
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
     * @param PromptCategory $category
     *
     * @return bool
     */
    public function deletePromptCategory($category) {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (Prompt::where('prompt_category_id', $category->id)->exists()) {
                throw new \Exception('An prompt with this category exists. Please change its category first.');
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
    public function sortPromptCategory($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                PromptCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        PROMPTS

    **********************************************************************************************/

    /**
     * Creates a new prompt.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Prompt
     */
    public function createPrompt($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['prompt_category_id']) && $data['prompt_category_id'] == 'none') {
                $data['prompt_category_id'] = null;
            }

            if ((isset($data['prompt_category_id']) && $data['prompt_category_id']) && !PromptCategory::where('id', $data['prompt_category_id'])->exists()) {
                throw new \Exception('The selected prompt category is invalid.');
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

            if (!isset($data['hide_submissions']) && !$data['hide_submissions']) {
                $data['hide_submissions'] = 0;
            }

            $prompt = Prompt::create(Arr::only($data, ['prompt_category_id', 'name', 'summary', 'description', 'parsed_description', 'is_active', 'start_at', 'end_at', 'hide_before_start', 'hide_after_end', 'has_image', 'prefix', 'hide_submissions', 'staff_only', 'hash']));

            if ($image) {
                $this->handleImage($image, $prompt->imagePath, $prompt->imageFileName);
            }

            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $prompt);

            return $this->commitReturn($prompt);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a prompt.
     *
     * @param Prompt                $prompt
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Prompt
     */
    public function updatePrompt($prompt, $data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['prompt_category_id']) && $data['prompt_category_id'] == 'none') {
                $data['prompt_category_id'] = null;
            }

            // More specific validation
            if (Prompt::where('name', $data['name'])->where('id', '!=', $prompt->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            if ((isset($data['prompt_category_id']) && $data['prompt_category_id']) && !PromptCategory::where('id', $data['prompt_category_id'])->exists()) {
                throw new \Exception('The selected prompt category is invalid.');
            }
            if (isset($data['prefix']) && Prompt::where('prefix', $data['prefix'])->where('id', '!=', $prompt->id)->exists()) {
                throw new \Exception('That prefix has already been taken.');
            }

            $data = $this->populateData($data, $prompt);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            if (!isset($data['hide_submissions']) && !$data['hide_submissions']) {
                $data['hide_submissions'] = 0;
            }

            $prompt->update(Arr::only($data, ['prompt_category_id', 'name', 'summary', 'description', 'parsed_description', 'is_active', 'start_at', 'end_at', 'hide_before_start', 'hide_after_end', 'has_image', 'prefix', 'hide_submissions', 'staff_only', 'hash']));

            if ($prompt) {
                $this->handleImage($image, $prompt->imagePath, $prompt->imageFileName);
            }

            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $prompt);

            return $this->commitReturn($prompt);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a prompt.
     *
     * @param Prompt $prompt
     *
     * @return bool
     */
    public function deletePrompt($prompt) {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (Submission::where('prompt_id', $prompt->id)->exists()) {
                throw new \Exception('A submission under this prompt exists. Deleting the prompt will break the submission page - consider setting the prompt to be not active instead.');
            }

            $prompt->rewards()->delete();
            if ($prompt->has_image) {
                $this->deleteImage($prompt->imagePath, $prompt->imageFileName);
            }
            $prompt->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handle category data.
     *
     * @param array               $data
     * @param PromptCategory|null $category
     *
     * @return array
     */
    private function populateCategoryData($data, $category = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        } elseif (!isset($data['description']) && !$data['description']) {
            $data['parsed_description'] = null;
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
     * Processes user input for creating/updating a prompt.
     *
     * @param array  $data
     * @param Prompt $prompt
     *
     * @return array
     */
    private function populateData($data, $prompt = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (!isset($data['hide_before_start'])) {
            $data['hide_before_start'] = 0;
        }
        if (!isset($data['hide_after_end'])) {
            $data['hide_after_end'] = 0;
        }
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        if (!isset($data['staff_only'])) {
            $data['staff_only'] = 0;
        }

        if (isset($data['remove_image'])) {
            if ($prompt && $prompt->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($prompt->imagePath, $prompt->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Processes user input for creating/updating prompt rewards.
     *
     * @param array  $data
     * @param Prompt $prompt
     */
    private function populateRewards($data, $prompt) {
        // Clear the old rewards...
        $prompt->rewards()->delete();

        if (isset($data['rewardable_type'])) {
            foreach ($data['rewardable_type'] as $key => $type) {
                PromptReward::create([
                    'prompt_id'       => $prompt->id,
                    'rewardable_type' => $type,
                    'rewardable_id'   => $data['rewardable_id'][$key],
                    'quantity'        => $data['quantity'][$key],
                ]);
            }
        }
    }
}
