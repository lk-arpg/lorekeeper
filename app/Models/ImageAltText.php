<?php

namespace App\Models;

class ImageAltText extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_model', 'object_id', 'alt_text', 'text_key',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'image_alt_text';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the object associated with the alt text.
     *
     * @param string $text_key
     */
    public function object($text_key) {
        return $this->morphTo('object', 'object_model', 'object_id')->where('text_key', $text_key);
    }

    /**********************************************************************************************

    OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks if a certain object has any alt text.
     * This is kept for backwards compatibility, and forwards to the helper function.
     *
     * @param mixed  $object
     * @param string $text_key
     */
    public static function hasAltText($object, $text_key) {
        return hasAltText($object, $text_key);
    }

    /**
     * Get the alt text of a certain object.
     * This is kept for backwards compatibility, and forwards to the helper function.
     *
     * @param mixed  $object
     * @param string $text_key
     */
    public static function getAltText($object, $text_key) {
        return getAltText($object, $text_key);
    }
}
