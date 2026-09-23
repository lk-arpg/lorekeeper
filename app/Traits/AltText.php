<?php

namespace App\Traits;

use App\Models\ImageAltText;

/**
 * Add this trait to any model that you want to have alt text.
 */
trait AltText {
    /**
     * Return the object's alt text.
     *
     * @param string $text_key
     *
     * @return mixed
     */
    public function altText($text_key) {
        // there should like. only be one of these so i think this is fine.
        // i dont remember how relations work. it has been like 90 years. if this is egregious please don't explode me.
        return $this->morphMany(ImageAltText::class, 'object', 'object_model', 'object_id')->where('text_key', $text_key)->first();
    }

    /**
     * Check if an object has alt text.
     *
     * @param string $text_key
     *
     * @return bool
     */
    public function hasAltText($text_key) {
        return $this->altText($text_key);
    }
}
