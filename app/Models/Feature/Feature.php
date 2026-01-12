<?php

namespace App\Models\Feature;

use App\Models\Model;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Feature extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'feature_category_id', 'species_id', 'rarity_id', 'name', 'has_image', 'description', 'parsed_description', 'is_visible', 'hash',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'features';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'feature_category_id' => 'nullable',
        'species_id'          => 'nullable',
        'rarity_id'           => 'required|exists:rarities,id',
        'name'                => 'required|unique:features|between:3,100',
        'description'         => 'nullable',
        'image'               => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'feature_category_id' => 'nullable',
        'species_id'          => 'nullable',
        'rarity_id'           => 'required|exists:rarities,id',
        'name'                => 'required|between:3,100',
        'description'         => 'nullable',
        'image'               => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the rarity of this feature.
     */
    public function rarity() {
        return $this->belongsTo(Rarity::class);
    }

    /**
     * Get the species the feature belongs to.
     */
    public function species() {
        return $this->belongsTo(Species::class);
    }

    /**
     * Get the category the feature belongs to.
     */
    public function category() {
        return $this->belongsTo(FeatureCategory::class, 'feature_category_id');
    }

    /**
     * Get the subtypes of this feature.
     */
    public function subtypes() {
        return $this->belongsToMany(Subtype::class, 'feature_subtypes');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort features in alphabetical order.
     *
     * @param Builder $query
     * @param bool    $reverse
     *
     * @return Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort features in category order.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeSortCategory($query) {
        if (FeatureCategory::all()->count()) {
            return $query->orderBy(FeatureCategory::select('sort')->whereColumn('features.feature_category_id', 'feature_categories.id'), 'DESC');
        }

        return $query;
    }

    /**
     * Scope a query to sort features in species order.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeSortSpecies($query) {
        $ids = Species::orderBy('sort', 'DESC')->pluck('id')->toArray();

        return count($ids) ? $query->orderBy(DB::raw('FIELD(species_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort features in subtype order.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeSortSubtype($query) {
        $ids = Subtype::orderBy('sort', 'DESC')->pluck('id')->toArray();

        if (count($ids)) {
            $ordered_subtypes = $this->subtypes()->latest('id')->pluck('id')->toArray();
            foreach ($ordered_subtypes as $subtype) {
                return $query->with('feature_subtypes')->orderBy(DB::raw($subtype.', '.implode(',', $ids).')'));
            }
        }

        return $query;
    }

    /**
     * Scope a query to sort features in rarity order.
     *
     * @param Builder $query
     * @param bool    $reverse
     *
     * @return Builder
     */
    public function scopeSortRarity($query, $reverse = false) {
        $ids = Rarity::orderBy('sort', $reverse ? 'ASC' : 'DESC')->pluck('id')->toArray();

        return count($ids) ? $query->orderBy(DB::raw('FIELD(rarity_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort features by newest first.
     *
     * @param Builder $query
     * @param mixed   $reverse
     *
     * @return Builder
     */
    public function scopeSortNewest($query, $reverse = false) {
        return $query->orderBy('id', $reverse ? 'ASC' : 'DESC');
    }

    /**
     * Scope a query to show only visible features.
     *
     * @param Builder    $query
     * @param mixed|null $user
     *
     * @return Builder
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
        return '<a href="'.$this->url.'" class="display-trait">'.$this->name.'</a>'.($this->rarity ? ' ('.$this->rarity->displayName.')' : '');
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/traits';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-'.$this->hash.'-image.png';
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
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/traits?name='.$this->name);
    }

    /**
     * Gets the URL for a masterlist search of characters in this category.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        return url('masterlist?feature_ids[]='.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/traits/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**********************************************************************************************

        Other Functions

    **********************************************************************************************/

    /**
     * Gets the trait's subtypes that are visible to the current user.
     *
     * @param mixed|null $user
     */
    public function getSubtypes($user = null) {
        return $this->subtypes()->visible($user)->get();
    }

    /**
     * Displays the trait's subtypes as an imploded string.
     *
     * @param mixed|null $user
     */
    public function displaySubtypes($user = null) {
        if (!count($this->subtypes()->visible($user)->get())) {
            return 'None';
        }
        $subtypes = [];
        foreach ($this->subtypes()->visible($user)->get() as $subtype) {
            $subtypes[] = $subtype->displayName;
        }

        return implode(', ', $subtypes);
    }

    public static function getDropdownItems($withHidden = 0, $withSpecies = 0) {
        $visibleOnly = 1;
        if ($withHidden) {
            $visibleOnly = 0;
        }

        if (config('lorekeeper.extensions.organised_traits_dropdown.enable')) {
            $sorted_feature_categories = collect(FeatureCategory::all()->where('is_visible', '>=', $visibleOnly)->sortBy('sort')->pluck('name')->toArray());

            if (config('lorekeeper.extensions.show_exclusively_species_traits_in_dropdown') && $withSpecies) {
                $grouped = self::where('is_visible', '>=', $visibleOnly)
                    ->when($withSpecies, function (Builder $query, int $withSpecies) {
                        $query->where('species_id', '=', $withSpecies)
                            ->orWhere('species_id', '=', null);
                    })
                    ->select('name', 'id', 'feature_category_id', 'rarity_id', 'species_id')->with(['category', 'rarity', 'species', 'subtypes'])
                    ->orderBy('name')->get()->keyBy('id')->groupBy('category.name', $preserveKeys = true)
                    ->toArray();
            } else {
                $grouped = self::where('is_visible', '>=', $visibleOnly)
                    ->select('name', 'id', 'feature_category_id', 'rarity_id', 'species_id')->with(['category', 'rarity', 'species', 'subtypes'])
                    ->orderBy('name')->get()->keyBy('id')->groupBy('category.name', $preserveKeys = true)
                    ->toArray();
            }
            if (isset($grouped[''])) {
                if (!$sorted_feature_categories->contains('Miscellaneous')) {
                    $sorted_feature_categories->push('Miscellaneous');
                }
                $grouped['Miscellaneous'] ??= [] + $grouped[''];
            }

            $sorted_feature_categories = $sorted_feature_categories->filter(function ($value, $key) use ($grouped) {
                return in_array($value, array_keys($grouped), true);
            });

            // Sort by rarity if enabled
            if (config('lorekeeper.extensions.organised_traits_dropdown.rarity.sort_by_rarity')) {
                foreach ($grouped as $category => &$features) { // &$features to modify the array in place
                    uasort($features, function ($a, $b) {
                        $sortA = $a['rarity']['sort'] ?? -1;
                        $sortB = $b['rarity']['sort'] ?? -1;

                        if ($sortA == $sortB) {
                            return strnatcasecmp($a['name'], $b['name']);
                        }

                        return $sortA <=> $sortB;
                    });
                }
                unset($features); // break the reference
            }

            foreach ($grouped as $category => $features) {
                foreach ($features as $id  => $feature) {
                    $grouped[$category][$id] = $feature['name'].
                    (
                        config('lorekeeper.extensions.organised_traits_dropdown.display_species') && $feature['species_id'] ?
                        ' <span class="text-muted"><small>'.$feature['species']['name'].'</small></span>'
                        : ''
                    ).
                    (
                        config('lorekeeper.extensions.organised_traits_dropdown.display_subtype') && count($feature['subtypes']) ?
                        ' <span class="text-muted"><small>('.implode(', ', array_map(
                            function (array $subtype) {
                                return $subtype['name'];
                            },
                            $feature['subtypes']
                        )).')</small></span>'
                        : ''
                    ).
                    ( // rarity
                        config('lorekeeper.extensions.organised_traits_dropdown.rarity.enable') && $feature['rarity'] ?
                        ' (<span '.($feature['rarity']['color'] ? 'style="color: #'.$feature['rarity']['color'].';"' : '').'>'.Rarity::find($feature['rarity']['id'])->name.'</span>)'
                        : ''
                    );
                }
            }
            $features_by_category = $sorted_feature_categories->map(function ($category) use ($grouped) {
                return [$category => $grouped[$category]];
            });

            return $features_by_category;
        } else {
            if (config('lorekeeper.extensions.show_exclusively_species_traits_in_dropdown') && $withSpecies) {
                return self::where('is_visible', '>=', $visibleOnly)
                    ->when($withSpecies, function (Builder $query, int $withSpecies) {
                        $query->where('species_id', '=', $withSpecies)
                            ->orWhere('species_id', '=', null);
                    })
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray();
            } else {
                return self::where('is_visible', '>=', $visibleOnly)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray();
            }
        }
    }
}
