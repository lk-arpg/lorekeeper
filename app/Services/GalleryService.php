<?php

namespace App\Services;

use App\Models\Gallery\Gallery;
use App\Models\Gallery\GalleryCriterion;
use App\Models\Gallery\GallerySubmission;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GalleryService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Gallery Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of galleries.
    |
    */

    /**
     * Creates a new gallery.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Gallery|bool
     */
    public function createGallery($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['submissions_open'])) {
                $data['submissions_open'] = 0;
            }
            if (!isset($data['currency_enabled'])) {
                $data['currency_enabled'] = 0;
            }
            if (!isset($data['votes_required'])) {
                $data['votes_required'] = 0;
            }
            if (!isset($data['hide_before_start'])) {
                $data['hide_before_start'] = 0;
            }
            if (!isset($data['prompt_selection'])) {
                $data['prompt_selection'] = 0;
            }

            $gallery = Gallery::create($data);

            (new CriterionService)->populateCriteria(Arr::only($data, ['criterion_id', 'criterion', 'criterion_currency_id', 'default_criteria']), $gallery, GalleryCriterion::class);

            if (!$this->logAdminAction($user, 'Created Gallery', 'Created '.$gallery->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($gallery);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a gallery.
     *
     * @param \App\Models\Gallery   $gallery
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Gallery|bool
     */
    public function updateGallery($gallery, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Gallery::where('name', $data['name'])->where('id', '!=', $gallery->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            if (!isset($data['submissions_open'])) {
                $data['submissions_open'] = 0;
            }
            if (!isset($data['currency_enabled'])) {
                $data['currency_enabled'] = 0;
            }
            if (!isset($data['votes_required'])) {
                $data['votes_required'] = 0;
            }
            if (!isset($data['hide_before_start'])) {
                $data['hide_before_start'] = 0;
            }
            if (!isset($data['prompt_selection'])) {
                $data['prompt_selection'] = 0;
            }

            if (!$this->logAdminAction($user, 'Updated Gallery', 'Updated '.$gallery->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            $gallery->update($data);

            (new CriterionService)->populateCriteria(Arr::only($data, ['criterion_id', 'criterion', 'criterion_currency_id', 'default_criteria']), $gallery, GalleryCriterion::class);

            return $this->commitReturn($gallery);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a gallery.
     *
     * @param \App\Models\Gallery $gallery
     * @param mixed               $user
     *
     * @return bool
     */
    public function deleteGallery($gallery, $user) {
        DB::beginTransaction();

        try {
            // Check first if submissions exist in this gallery, or the gallery has children
            if (GallerySubmission::where('gallery_id', $gallery->id)->exists() || Gallery::where('parent_id', $gallery->id)->exists()) {
                throw new \Exception("A gallery or submissions in this gallery exist. Consider setting the gallery's submissions to closed instead.");
            }
            if (!$this->logAdminAction($user, 'Deleted Gallery', 'Deleted '.$gallery->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            $gallery->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
