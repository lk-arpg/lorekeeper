<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttachmentService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Attachment Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of attachments on objects
    |
    */

    /**********************************************************************************************

        ATTACHMENTS

    **********************************************************************************************/

    /**
     * Creates or edits attachments on an object.
     * Deletes existing attachments for the given parent, then recreates from provided data.
     *
     * @param string $parent_model
     * @param int    $parent_id
     * @param array  $data
     * @param mixed  $log
     *
     * @return bool
     */
    public function editAttachments($parent_model, $parent_id, $data, $log = true) {
        DB::beginTransaction();

        try {
            $parent = $parent_model::find($parent_id);
            if (!$parent) {
                throw new \Exception('Object not found.');
            }

            $attachments = hasAttachments($parent) ? getAttachments($parent) : [];
            if (count($attachments) > 0) {
                $attachments->each(function ($attachment) {
                    $attachment->delete();
                });
            }

            if (count($attachments) > 0) {
                flash('Deleted '.count($attachments).' old attachments.')->success();
            }

            if (isset($data['attachment_type'])) {
                foreach ($data['attachment_type'] as $key => $type) {
                    $attributes = [
                        'parent_model'     => $parent_model,
                        'parent_id'        => $parent_id,
                        'attachment_type'  => $data['attachment_type'][$key],
                        'attachment_id'    => $data['attachment_id'][$key],
                        'data'             => null,
                    ];

                    if (isset($data['data'][$key])) {
                        // description is special we also store parsed_description
                        if (isset($data['data'][$key]['description'])) {
                            $attributes['data']['description'] = $data['data'][$key]['description'];
                            $attributes['data']['parsed_description'] = parse($data['data'][$key]['description']);

                            unset($data['data'][$key]['description']);
                        }
                        // additional "special" fields can be mapped here in the future.

                        // any other fields are stored as-is
                        foreach ($data['data'][$key] as $field_key => $field_value) {
                            $attributes['data'][$field_key] = $field_value;
                        }
                    }

                    $attachment = new Attachment([
                        'parent_model'     => $parent_model,
                        'parent_id'        => $parent_id,
                        'attachment_type'  => $data['attachment_type'][$key],
                        'attachment_id'    => $data['attachment_id'][$key],
                        'data'             => $attributes['data'] ?? null,
                    ]);

                    if (!$attachment->save()) {
                        throw new \Exception('Failed to save attachment.');
                    }
                }
            }

            // Log the action
            if ($log && !$this->logAdminAction(Auth::user(), 'Edited Attachments', 'Edited '.$parent->displayName.' attachments')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
