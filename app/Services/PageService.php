<?php

namespace App\Services;

use App\Models\SitePage;
use Illuminate\Support\Facades\DB;

class PageService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Page Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of site pages.
    |
    */

    /**
     * Creates a site page.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|SitePage
     */
    public function createPage($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['text']) && $data['text']) {
                $data['parsed_text'] = parse($data['text']);
            }
            $data['user_id'] = $user->id;
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['can_comment'])) {
                $data['can_comment'] = 0;
                $data['allow_dislikes'] = 0;
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $page = SitePage::create($data);

            if (isset($data['remove_image'])) {
                if ($page && $page->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    $image = null;
                    $this->deleteImage($page->imagePath, $page->imageFileName);
                }
                unset($data['remove_image']);
            }

            if ($image) {
                $this->handleImage($image, $page->imagePath, $page->imageFileName);
            }

            return $this->commitReturn($page);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a site page.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $page
     *
     * @return bool|SitePage
     */
    public function updatePage($page, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (SitePage::where('key', $data['key'])->where('id', '!=', $page->id)->exists()) {
                throw new \Exception('The key has already been taken.');
            }

            if (isset($data['text']) && $data['text']) {
                $data['parsed_text'] = parse($data['text']);
            }
            $data['user_id'] = $user->id;
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['can_comment'])) {
                $data['can_comment'] = 0;
                $data['allow_dislikes'] = 0;
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $page->update($data);

            if (isset($data['remove_image'])) {
                if ($page && $page->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    $image = null;
                    $this->deleteImage($page->imagePath, $page->imageFileName);
                }
                unset($data['remove_image']);
            }

            if ($page) {
                $this->handleImage($image, $page->imagePath, $page->imageFileName);
            }

            return $this->commitReturn($page);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a site page.
     *
     * @param mixed $page
     *
     * @return bool
     */
    public function deletePage($page) {
        DB::beginTransaction();

        try {
            // Specific pages such as the TOS/privacy policy cannot be deleted from the admin panel.
            if (config('lorekeeper.text_pages.'.$page->key)) {
                throw new \Exception('You cannot delete this page.');
            }

            if ($page->has_image) {
                $this->deleteImage($page->imagePath, $page->imageFileName);
            }

            $page->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Regenerates a site page.
     *
     * @param mixed $page
     *
     * @return bool
     */
    public function regenPage($page) {
        DB::beginTransaction();

        try {
            $page->parsed_text = parse($page->text);

            $page->save();

            return $this->commitReturn($page);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
