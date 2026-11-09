<?php

namespace App\Models\Character;

use App\Models\Model;
use App\Models\User\User;

class CharacterBookmarkFolder extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'sort',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_bookmark_folders';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|string|max:50',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|string|max:50',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user the folder belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bookmarks contained in this folder.
     */
    public function bookmarks() {
        return $this->hasMany(CharacterBookmark::class, 'folder_id');
    }
}
