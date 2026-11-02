<?php

namespace App\Models;

class Rarity extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sort', 'color', 'has_image', 'description', 'parsed_description', 'hash',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rarities';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:rarities|between:3,100',
        'color'       => 'nullable|regex:/^#?[0-9a-fA-F]{6}$/i',
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
        'color'       => 'nullable|regex:/^#?[0-9a-fA-F]{6}$/i',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-rarity" '.($this->color ? 'style="color: #'.$this->color.';"' : '').'>'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/rarities';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getRarityImageFileNameAttribute() {
        return $this->id.'-'.$this->hash.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getRarityImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getRarityImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->rarityImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/rarities?name='.$this->name);
    }

    /**
     * Gets the URL for an encyclopedia search of features (character traits) of this rarity.
     *
     * @return string
     */
    public function getSearchFeaturesUrlAttribute() {
        return url('world/traits?rarity_id='.$this->id);
    }

    /**
     * Gets the URL for an encyclopedia search of items of this rarity.
     */
    public function getSearchItemsUrlAttribute() {
        return url('world/items?rarity_id='.$this->id);
    }

    /**
     * Gets the URL for a masterlist search of characters of this rarity.
     *
     * @return string
     */
    public function getSearchCharactersUrlAttribute() {
        return url('masterlist?rarity_id='.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/rarities/edit/'.$this->id);
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
