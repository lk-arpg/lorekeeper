<?php

namespace App\Models;

class Attachment extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_model', 'parent_id', 'attachment_type', 'attachment_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attachments';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the parent object this is attached to.
     */
    public function parent() {
        return $this->belongsTo($this->parent_model, 'parent_id');
    }

    /**
     * Get the attached object.
     */
    public function attachment() {
        $model = getAssetModelString(strtolower($this->attachment_type));

        if (!class_exists($model)) {
            // Laravel requires a relationship instance to be returned (cannot return null), so returning one that doesn't exist here.
            return $this->belongsTo(self::class, 'id', 'attachment_id')->whereNull('attachment_id');
        }

        return $this->belongsTo($model, 'attachment_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns the description attribute or null if not set.
     */
    public function getDescriptionAttribute() {
        return $this->data['description'] ?? null;
    }

    /**
     * Returns the parsed description attribute or null if not set.
     */
    public function getParsedDescriptionAttribute() {
        return $this->data['parsed_description'] ?? null;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks if a certain object has any attachments.
     *
     * @param mixed $object
     */
    public static function hasAttachments($object) {
        return self::where('parent_model', get_class($object))->where('parent_id', $object->id)->exists();
    }

    /**
     * Get the attachments of a certain object.
     *
     * @param mixed $object
     */
    public static function getAttachments($object) {
        return self::where('parent_model', get_class($object))->where('parent_id', $object->id)->get();
    }
}
