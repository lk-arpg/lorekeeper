<?php

namespace App\Models\Character;

use App\Models\Currency\Currency;
use App\Models\Model;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharacterDesignUpdate extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'status', 'user_id', 'staff_id',
        'comments', 'staff_comments', 'data', 'extension',
        'use_cropper', 'x0', 'x1', 'y0', 'y1',
        'hash', 'species_id', 'subtype_ids', 'rarity_id',
        'has_comments', 'has_image', 'has_addons', 'has_features',
        'submitted_at', 'update_type', 'fullsize_hash',
        'vote_data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'design_updates';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'subtype_ids'  => 'array',
        'data'         => 'array',
        'vote_data'    => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for uploaded images.
     *
     * @var array
     */
    public static $imageRules = [
        'image'          => 'nullable|mimes:jpeg,gif,png',
        'thumbnail'      => 'nullable|mimes:jpeg,gif,png',
        'artist_url.*'   => 'nullable|url',
        'designer_url.*' => 'nullable|url',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the character associated with the design update.
     */
    public function character() {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Get the user who created the design update.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the staff who processed the design update.
     */
    public function staff() {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the species of the design update.
     */
    public function species() {
        return $this->belongsTo(Species::class, 'species_id');
    }

    /**
     * Get the rarity of the design update.
     */
    public function rarity() {
        return $this->belongsTo(Rarity::class, 'rarity_id');
    }

    /**
     * Get the features (traits) attached to the design update, ordered by display order.
     */
    public function features() {
        $query = $this
            ->hasMany(CharacterFeature::class, 'character_image_id')->where('character_features.character_type', 'Update')
            ->join('features', 'features.id', '=', 'character_features.feature_id')
            ->leftJoin('feature_categories', 'feature_categories.id', '=', 'features.feature_category_id')
            ->select(['character_features.*', 'features.*', 'character_features.id AS character_feature_id', 'feature_categories.sort']);

        return $query->orderByDesc('sort');
    }

    /**
     * Get the features (traits) attached to the design update with no extra sorting.
     */
    public function rawFeatures() {
        return $this->hasMany(CharacterFeature::class, 'character_image_id')->where('character_features.character_type', 'Update');
    }

    /**
     * Get the designers attached to the design update.
     */
    public function designers() {
        return $this->hasMany(CharacterImageCreator::class, 'character_image_id')->where('type', 'Designer')->where('character_type', 'Update');
    }

    /**
     * Get the artists attached to the design update.
     */
    public function artists() {
        return $this->hasMany(CharacterImageCreator::class, 'character_image_id')->where('type', 'Artist')->where('character_type', 'Update');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active (Open or Pending) update requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('status', '!=', 'Approved')->where('status', '!=', 'Rejected');
    }

    /**
     * Scope a query to only include MYO slot approval requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMyos($query) {
        $query->select('design_updates.*')->where('update_type', 'MYO');
    }

    /**
     * Scope a query to only include character design update requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCharacters($query) {
        $query->select('design_updates.*')->where('update_type', 'Character');
    }

    /**
     * Scope a query to sort updates by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query, $reverse = false) {
        return $query->orderBy('id', $reverse ? 'ASC' : 'DESC');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the items (UserItem IDs) attached to this update request.
     *
     * @return array
     */
    public function getInventoryAttribute() {
        // This is for showing the addons page
        // just need to retrieve a list of stack IDs to tell which ones to check

        return $this->data && isset($this->data['user']['user_items']) ? $this->data['user']['user_items'] : [];
    }

    /**
     * Get the user-owned currencies attached to this update request.
     *
     * @return array
     */
    public function getUserBankAttribute() {
        return $this->data && isset($this->data['user']['currencies']) ? $this->data['user']['currencies'] : [];
    }

    /**
     * Get the character-owned currencies attached to this update request.
     *
     * @return array
     */
    public function getCharacterBankAttribute() {
        return $this->data && isset($this->data['character']['currencies']) ? $this->data['character']['currencies'] : [];
    }

    /**
     * Check if all sections of the form have been touched.
     *
     * @return bool
     */
    public function getIsCompleteAttribute() {
        return $this->has_comments && $this->has_image && $this->has_addons && $this->has_features;
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/character-updates/'.floor($this->id / 1000);
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'_'.$this->hash.'.'.$this->extension;
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the file name of the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailFileNameAttribute() {
        if (config('lorekeeper.settings.masterlist_image_format') != null && config('lorekeeper.settings.masterlist_image_format') != $this->extension) {
            $extension = config('lorekeeper.settings.masterlist_image_format');
        } else {
            $extension = $this->extension;
        }

        return $this->id.'_'.$this->hash.'_th.'.$extension;
    }

    /**
     * Gets the path to the file directory containing the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailPathAttribute() {
        return $this->imagePath;
    }

    /**
     * Gets the URL of the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailUrlAttribute() {
        return asset($this->imageDirectory.'/'.$this->thumbnailFileName);
    }

    /**
     * Gets the URL of the design update request.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('designs/'.$this->id);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Get the available currencies that the user can attach to this update request.
     *
     * @param string $type
     *
     * @return array
     */
    public function getBank($type) {
        if ($type == 'user') {
            $currencies = $this->userBank;
        } else {
            $currencies = $this->characterBank;
        }
        if (!count($currencies)) {
            return [];
        }
        $ids = array_keys($currencies);
        $result = Currency::whereIn('id', $ids)->get();
        foreach ($result as $i=> $currency) {
            $currency->quantity = $currencies[$currency->id];
        }

        return $result;
    }

    /**
     * Get the subtypes of the design update.
     */
    public function subtypes() {
        return $this->subtype_ids;
    }

    /**
     * Get the subtypes of the design update.
     */
    public function displaySubtypes() {
        $subtypes = $this->subtypes();
        $result = [];
        foreach ($subtypes as $subtype) {
            $result[] = Subtype::find($subtype)->displayName;
        }

        return implode(', ', $result);
    }

    /**
     * Gets the voting data of the gallery submission and performs preliminary processing.
     *
     * @param bool $withUsers
     *
     * @return array
     */
    public function getVoteData($withUsers = 0) {
        $voteData['raw'] = $this->vote_data;

        // Only query users if necessary, and condense to one query per submission
        if ($withUsers) {
            $users = User::whereIn('id', array_keys($voteData['raw']))->select('id', 'name', 'rank_id')->get();
        } else {
            $users = null;
        }

        $voteData['raw'] = collect($voteData['raw'])->mapWithKeys(function ($vote, $id) use ($users) {
            return [$id => [
                'vote' => $vote,
                'user' => $users ? $users->where('id', $id)->first() : $id,
            ]];
        });

        // Tally approve/reject sums for ease
        $voteData['approve'] = $voteData['reject'] = 0;
        foreach ($voteData['raw'] as $vote) {
            switch ($vote['vote']) {
                case 1:
                    $voteData['reject'] += 1;
                    break;
                case 2:
                    $voteData['approve'] += 1;
                    break;
            }
        }

        return $voteData;
    }
}
