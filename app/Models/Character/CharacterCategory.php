<?php

namespace App\Models\Character;

use App\Models\Model;

class CharacterCategory extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name', 'sort', 'has_image', 'description', 'parsed_description', 'masterlist_sub_id', 'is_visible', 'hash',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_categories';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:character_categories|between:3,100',
        'code'        => 'required|unique:character_categories|between:1,25',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|between:3,100',
        'code'        => 'required|between:1,25',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the sub masterlist for this species.
     */
    public function sublist() {
        return $this->belongsTo(Sublist::class, 'masterlist_sub_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to show only visible categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-category">'.$this->name.' ('.$this->code.')</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/character-categories';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getCategoryImageFileNameAttribute() {
        return $this->id.'-'.$this->hash.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getCategoryImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getCategoryImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->categoryImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/character-categories?name='.$this->name);
    }

    /**
     * Gets the URL for a masterlist search of characters in this category.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        if ($this->masterlist_sub_id != 0 && $this->sublist->show_main == 0) {
            return url('sublist/'.$this->sublist->key.'?character_category_id='.$this->id);
        } else {
            return url('masterlist?character_category_id='.$this->id);
        }
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/character-categories/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }
}
