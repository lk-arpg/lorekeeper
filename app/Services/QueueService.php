<?php
namespace App\Services;

use App\Models\Queue\Queue;
use App\Models\Queue\QueueCategory;
use App\Models\Queue\QueueSubmission;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class QueueService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Queue Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of queue categories and queues.
    |
    */

    /**********************************************************************************************

        QUEUE CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|QueueCategory
     */
    public function createQueueCategory($data, $user)
    {
        DB::beginTransaction();

        try {
            $data = $this->populateCategoryData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash']      = randomString(10);
                $image             = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $category = QueueCategory::create($data);

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
     * @param QueueCategory        $category
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|QueueCategory
     */
    public function updateQueueCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if (QueueCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateCategoryData($data, $category);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash']      = randomString(10);
                $image             = $data['image'];
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
     * @param QueueCategory $category
     *
     * @return bool
     */
    public function deleteQueueCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (Queue::where('queue_category_id', $category->id)->exists()) {
                throw new \Exception('An queue with this category exists. Please change its category first.');
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
    public function sortQueueCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                QueueCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        QUEUES

    **********************************************************************************************/

    /**
     * Creates a new queue.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Queue
     */
    public function createQueue($data, $user)
    {
        DB::beginTransaction();

        try {
            if (isset($data['queue_category_id']) && $data['queue_category_id'] == 'none') {
                $data['queue_category_id'] = null;
            }

            if ((isset($data['queue_category_id']) && $data['queue_category_id']) && ! QueueCategory::where('id', $data['queue_category_id'])->exists()) {
                throw new \Exception('The selected queue category is invalid.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash']      = randomString(10);
                $image             = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            if (! isset($data['hide_submissions']) && ! $data['hide_submissions']) {
                $data['hide_submissions'] = 0;
            }

            $queue = Queue::create($data);

            if ($image) {
                $this->handleImage($image, $queue->imagePath, $queue->imageFileName);
            }

            return $this->commitReturn($queue);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a queue.
     *
     * @param Queue                $queue
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Queue
     */
    public function updateQueue($queue, $data, $user)
    {
        DB::beginTransaction();

        try {
            if (isset($data['queue_category_id']) && $data['queue_category_id'] == 'none') {
                $data['queue_category_id'] = null;
            }

            // More specific validation
            if (Queue::where('name', $data['name'])->where('id', '!=', $queue->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            if ((isset($data['queue_category_id']) && $data['queue_category_id']) && ! QueueCategory::where('id', $data['queue_category_id'])->exists()) {
                throw new \Exception('The selected queue category is invalid.');
            }
            if (isset($data['prefix']) && Queue::where('prefix', $data['prefix'])->where('id', '!=', $queue->id)->exists()) {
                throw new \Exception('That prefix has already been taken.');
            }

            $data = $this->populateData($data, $queue);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash']      = randomString(10);
                $image             = $data['image'];
                unset($data['image']);
            }

            if (! isset($data['hide_submissions']) && ! $data['hide_submissions']) {
                $data['hide_submissions'] = 0;
            }

            // clear data if changing type
            if ($queue->queue_type !== $data['queue_type']) {
                $queue->data = null;
                $queue->save();
            }

            $queue->update($data);

            if ($queue) {
                $this->handleImage($image, $queue->imagePath, $queue->imageFileName);
            }

            return $this->commitReturn($queue);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a queue.
     *
     * @param Queue $queue
     *
     * @return bool
     */
    public function deleteQueue($queue)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (QueueSubmission::where('queue_id', $queue->id)->exists()) {
                throw new \Exception('A submission under this queue exists. Deleting the queue will break the submission page - consider setting the queue to be not active instead.');
            }

            $queue->rewards()->delete();
            if ($queue->has_image) {
                $this->deleteImage($queue->imagePath, $queue->imageFileName);
            }
            $queue->delete();

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
     * @param QueueCategory|null $category
     *
     * @return array
     */
    private function populateCategoryData($data, $category = null)
    {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        } elseif (! isset($data['description']) && ! $data['description']) {
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
     * Processes user input for creating/updating a queue.
     *
     * @param array  $data
     * @param Queue $queue
     *
     * @return array
     */
    private function populateData($data, $queue = null)
    {

        if (! $data['queue_type']) {
            throw new \Exception("You must select a queue type. Choose vanilla if you want a blank queue.");
        }

        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (! isset($data['hide_before_start'])) {
            $data['hide_before_start'] = 0;
        }
        if (! isset($data['hide_after_end'])) {
            $data['hide_after_end'] = 0;
        }
        if (! isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        if (! isset($data['staff_only'])) {
            $data['staff_only'] = 0;
        }

        if (isset($data['form']) && $data['form']) {
            $data['parsed_form'] = parse($data['form']);
        }

        if (isset($data['remove_image'])) {
            if ($queue && $queue->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($queue->imagePath, $queue->imageFileName);
            }
            unset($data['remove_image']);
        }

        if (isset($data['check_text'])) {
            foreach ($data['check_text'] as $check) {
                if (! isset($check)) {
                    throw new \Exception('One of the checklist steps was not specified.');
                }
            }
            $data['checklist'] = $data['check_text'];
        }

        return $data;
    }

    /**
     * Update the queue's type data.
     *
     * @param  array  $data
     * @return bool
     */
    public function updateType($queue, $data)
    {
        DB::beginTransaction();

        try {
            if (isset($data['item_id'])) {
                foreach ($data['item_id'] as $item) {
                    if (! isset($item)) {
                        throw new \Exception('One of the items was not specified.');
                    }
                }
            }

            $queue->data = $queue->service->updateData($queue, $data) +
                [
                'items' => isset($data['item_id']) ? $data['item_id'] : null,
            ] + (isset($data['item_id']) ? [
                'items' => $data['item_id'],
            ] : []);
            $queue->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
