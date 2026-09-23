<?php

namespace App\Services;

use App\Models\ImageAltText;
use App\Traits\AltText;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AltTextService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Alt Text Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of alt text on objects
    |
    */

    /**********************************************************************************************

        ALT TEXT

    **********************************************************************************************/

    /**
     * Processes user input for creating/updating object alt text.
     *
     * @param mixed $data
     * @param mixed $object_model
     * @param mixed $object_id
     * @param mixed $data
     */
    public function createEditAltText($object_model, $object_id, $data) {
        DB::beginTransaction();

        try {
            $object = $object_model::find($object_id);
            if (!$object) {
                throw new \Exception('Object not found.');
            }
            if (!isset($data['text_key'])) {
                throw new \Exception('Key not given.');
            }
            if (!in_array(AltText::class, class_uses_recursive($object_model))) {
                throw new \Exception('Alt text is not supported for this model.');
            }

            $altText = $object->altText($data['text_key']);

            // would it be better to make individual copies of the function instead of changing the variable?
            // idk. i don't think it matters
            $cretype = 'Edited';
            if ($altText) {
                // existing entry
                if (!isset($data['alt_text'])) {
                    // no text. delet
                    $altText->delete();
                    $cretype = 'Deleted';
                } else {
                    // update existing entry
                    $altText->update(['alt_text' => $data['alt_text']]);
                }
            } else {
                // new entry
                if (!isset($data['alt_text'])) {
                    // no text. bad
                    throw new \Exception('You must enter alt text.');
                } else {
                    // create entry
                    $textData = [
                        'object_model'         => $object_model,
                        'object_id'            => $object_id,
                        'alt_text'             => $data['alt_text'],
                        'text_key'             => $data['text_key'],
                    ];
                    $textModel = ImageAltText::create($textData);

                    if (!$textModel) {
                        throw new \Exception('Failed to create alt text.');
                    }
                    $cretype = 'Created';
                }
            }

            // log the action
            if (!$this->logAdminAction(Auth::user(), $cretype.' Alt Text', $cretype.' '.$object->displayName.' alt text')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
