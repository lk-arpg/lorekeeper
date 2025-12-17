<?php

namespace App\Http\Controllers;

use App\Models\Character\CharacterCategory;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Rarity;
use App\Models\Shop\Shop;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorldController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | World Controller
    |--------------------------------------------------------------------------
    |
    | Displays information about the world, as entered in the admin panel.
    | Pages displayed by this controller form the site's encyclopedia.
    |
    */

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('world.index');
    }

    /**
     * Shows the currency categories page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCurrencyCategories(Request $request) {
        $query = CurrencyCategory::query();
        $name = $request->get('name');
        if ($name) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        return view('world.currency_categories', [
            'categories' => $query->visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->orderBy('id')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the currency page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCurrencies(Request $request) {
        $query = Currency::query()->visible(Auth::user() ?? null)->with('category')->where(function ($query) {
            $query->whereHas('category', function ($query) {
                $query->visible(Auth::user() ?? null);
            })->orWhereNull('currency_category_id');
        });

        $data = $request->only(['currency_category_id', 'name', 'sort']);
        if (isset($data['name'])) {
            $query->where(function ($query) use ($data) {
                $query->where('name', 'LIKE', '%'.$data['name'].'%')->orWhere('abbreviation', 'LIKE', '%'.$data['name'].'%');
            });
        }
        if (isset($data['currency_category_id'])) {
            if ($data['currency_category_id'] == 'withoutOption') {
                $query->whereNull('currency_category_id');
            } else {
                $query->where('currency_category_id', $data['currency_category_id']);
            }
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
            }
        } else {
            $query->sortCategory();
        }

        return view('world.currencies', [
            'currencies' => $query->visible(Auth::user() ?? null)->orderBy('name')->orderBy('id')->paginate(20)->appends($request->query()),
            'categories' => ['withoutOption' => 'Without Category'] + CurrencyCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the rarity page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRarities(Request $request) {
        $query = Rarity::query();
        $name = $request->get('name');
        if ($name) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        return view('world.rarities', [
            'rarities' => $query->orderBy('sort', 'DESC')->orderBy('id')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the species page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSpecieses(Request $request) {
        $query = Species::query();

        if (config('lorekeeper.extensions.visual_trait_index.enable_species_index')) {
            $query->withCount('features');
        }

        $data = $request->only(['name', 'sort']);

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'standard':
                    $query->sortStandard();
                    break;
                case 'standard-reverse':
                    $query->sortStandard(true);
                    break;
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
            }
        } else {
            $query->sortStandard();
        }

        return view('world.specieses', [
            'specieses' => $query->with(['subtypes' => function ($query) {
                $query->visible(Auth::user() ?? null)->sortStandard();
            }])->visible(Auth::user() ?? null)->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the subtypes page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubtypes(Request $request) {
        $query = Subtype::query()->with('species');
        $data = $request->only(['species_id', 'name', 'sort']);

        if (isset($data['species_id']) && $data['species_id'] != 'none') {
            $query->where('species_id', $data['species_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'standard':
                    $query->sortStandard();
                    break;
                case 'standard-reverse':
                    $query->sortStandard(true);
                    break;
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'species':
                    $query->sortSpecies();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
            }
        } else {
            $query->sortStandard();
        }

        return view('world.subtypes', [
            'subtypes'   => $query->visible(Auth::user() ?? null)->paginate(20)->appends($request->query()),
            'specieses'  => ['none' => 'Any Species'] + Species::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the item categories page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItemCategories(Request $request) {
        $query = ItemCategory::query();
        $name = $request->get('name');
        if ($name) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        return view('world.item_categories', [
            'categories' => $query->visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->orderBy('id')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the trait categories page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeatureCategories(Request $request) {
        $query = FeatureCategory::query();
        $name = $request->get('name');
        if ($name) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        return view('world.feature_categories', [
            'categories' => $query->visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->orderBy('id')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the traits page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeatures(Request $request) {
        $query = Feature::visible(Auth::user() ?? null)->with('category', 'rarity', 'species', 'subtypes');

        $data = $request->only(['rarity_id', 'feature_category_id', 'species_id', 'subtype_id', 'name', 'sort']);

        if (isset($data['rarity_id']) && $data['rarity_id'] != 'none') {
            $query->where('rarity_id', $data['rarity_id']);
        }
        if (isset($data['feature_category_id']) && $data['feature_category_id'] != 'none') {
            if ($data['feature_category_id'] == 'withoutOption') {
                $query->whereNull('feature_category_id');
            } else {
                $query->where('feature_category_id', $data['feature_category_id']);
            }
        }
        if (isset($data['species_id']) && $data['species_id'] != 'none') {
            if ($data['species_id'] == 'withoutOption') {
                $query->whereNull('species_id');
            } else {
                $query->where('species_id', $data['species_id']);
            }
        }
        if (isset($data['subtype_id']) && $data['subtype_id'] != 'none') {
            if ($data['subtype_id'] == 'withoutOption') {
                $query->doesntHave('subtypes');
            } else {
                if (isset($data['subtype_id']) && $data['subtype_id'] != 'none') {
                    $query->whereHas('subtypes', function ($query) use ($data) {
                        $query->where('subtype_id', $data['subtype_id']);
                    });
                }
            }
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'rarity':
                    $query->sortRarity();
                    break;
                case 'rarity-reverse':
                    $query->sortRarity(true);
                    break;
                case 'species':
                    $query->sortSpecies();
                    break;
                case 'subtypes':
                    $query->sortSubtype();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
            }
        } else {
            $query->sortCategory();
        }

        return view('world.features', [
            'features'   => $query->orderBy('id')->paginate(20)->appends($request->query()),
            'rarities'   => ['none' => 'Any Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses'  => ['none' => 'Any Species'] + ['withoutOption' => 'Without Species'] + Species::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'subtypes'   => ['none' => 'Any Subtype'] + ['withoutOption' => 'Without Subtype'] + Subtype::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'Any Category'] + ['withoutOption' => 'Without Category'] + FeatureCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows a species' visual trait list.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSpeciesFeatures($id) {
        $categories = FeatureCategory::orderBy('sort', 'DESC')->get();
        $rarities = Rarity::orderBy('sort', 'ASC')->get();

        $species = Species::visible(Auth::user() ?? null)->where('id', $id)->first();
        if (!$species) {
            abort(404);
        }
        if (!config('lorekeeper.extensions.visual_trait_index.enable_species_index')) {
            abort(404);
        }

        $features = $species->features()->visible(Auth::user() ?? null)->with('rarity', 'subtypes');
        $features = count($categories) ?
            $features->orderByRaw('FIELD(feature_category_id,'.implode(',', $categories->pluck('id')->toArray()).')') :
            $features;
        $features = $features->orderByRaw('FIELD(rarity_id,'.implode(',', $rarities->pluck('id')->toArray()).')')
            ->orderBy('has_image', 'DESC')
            ->orderBy('name')
            ->get()
            ->filter(function ($feature) {
                if (!$feature->subtypes->isEmpty()) {
                    return !$feature->subtypes->where('is_visible', true)->isEmpty();
                }

                return true;
            })
            ->groupBy(['feature_category_id', 'id']);

        return view('world.species_features', [
            'species'    => $species,
            'categories' => $categories->keyBy('id'),
            'rarities'   => $rarities->keyBy('id'),
            'features'   => $features,
        ]);
    }

    /**
     * Shows a subtype's visual trait list.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubtypeFeatures($id, Request $request) {
        $categories = FeatureCategory::orderBy('sort', 'DESC')->get();
        $rarities = Rarity::orderBy('sort', 'ASC')->get();
        $speciesBasics = $request->get('add_basics');
        $subtype = Subtype::visible(Auth::user() ?? null)->where('id', $id)->first();
        $species = Species::visible(Auth::user() ?? null)->where('id', $subtype->species->id)->first();
        if (!$subtype) {
            abort(404);
        }
        if (!config('lorekeeper.extensions.visual_trait_index.enable_subtype_index')) {
            abort(404);
        }

        $features = $speciesBasics ? $species : $subtype;
        $features = $features->features()->visible(Auth::user() ?? null);
        $features = count($categories) ?
            $features->orderByRaw('FIELD(feature_category_id,'.implode(',', $categories->pluck('id')->toArray()).')') :
            $features;
        $features = $features->orderByRaw('FIELD(rarity_id,'.implode(',', $rarities->pluck('id')->toArray()).')')
            ->orderBy('has_image', 'DESC')
            ->orderBy('name')
            ->get();

        if (!$speciesBasics) {
            $features = $features->groupBy(['feature_category_id', 'id']);
        } else {
            $features = $features
                ->filter(function ($feature) use ($subtype) {
                    return $feature->subtypes->isEmpty() || !$feature->subtypes->where('id', $subtype->id)->isEmpty();
                })
                ->groupBy(['feature_category_id', 'id']);
        }

        return view('world.subtype_features', [
            'subtype'    => $subtype,
            'categories' => $categories->keyBy('id'),
            'rarities'   => $rarities->keyBy('id'),
            'features'   => $features,
        ]);
    }

    /**
     * Shows a universal visual trait list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUniversalFeatures(Request $request) {
        $categories = FeatureCategory::orderBy('sort', 'DESC')->get();
        $rarities = Rarity::orderBy('sort', 'ASC')->get();

        if (!config('lorekeeper.extensions.visual_trait_index.enable_universal_index')) {
            abort(404);
        }

        $features = Feature::whereNull('species_id')
            ->visible(Auth::user() ?? null);
        $features = count($categories) ?
            $features->orderByRaw('FIELD(feature_category_id,'.implode(',', $categories->pluck('id')->toArray()).')') :
            $features;
        $features = count($rarities) ?
            $features->orderByRaw('FIELD(rarity_id,'.implode(',', $rarities->pluck('id')->toArray()).')') :
            $features;

        $features = $features
            ->orderBy('has_image', 'DESC')
            ->orderBy('name')
            ->get()->groupBy(['feature_category_id', 'id']);

        return view('world.universal_features', [
            'categories' => $categories->keyBy('id'),
            'rarities'   => $rarities->keyBy('id'),
            'features'   => $features,
        ]);
    }

    /**
     * Provides a single trait's description html for use in a modal.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeatureDetail($id) {
        $feature = Feature::visible(Auth::user() ?? null)->where('id', $id)->with('species', 'subtype', 'rarity')->first();

        if (!$feature) {
            abort(404);
        }

        return view('world._feature_entry', [
            'feature' => $feature,
        ]);
    }

    /**
     * Shows the visual trait list for all traits.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getKitchenSinkFeatures(Request $request) {
        $categories = FeatureCategory::orderBy('sort', 'DESC')->get();
        $rarities = Rarity::orderBy('sort', 'ASC')->get();

        $features = Feature::visible(Auth::user() ?? null);

        $features = count($categories) ?
            $features->orderByRaw('FIELD(feature_category_id,'.implode(',', $categories->pluck('id')->toArray()).')') :
            $features;

        $features = count($rarities) ?
            $features->orderByRaw('FIELD(rarity_id,'.implode(',', $rarities->pluck('id')->toArray()).')') :
            $features;

        $features = $features
            ->orderBy('has_image', 'DESC')
            ->orderBy('name')
            ->get()
            ->groupBy(['feature_category_id', 'id']);

        return view('world.kitchensink_features', [
            'categories' => $categories->keyBy('id'),
            'rarities'   => $rarities->keyBy('id'),
            'features'   => $features,
        ]);
    }

    /**
     * Shows the items page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItems(Request $request) {
        $query = Item::with('category')->released(Auth::user() ?? null);

        if (config('lorekeeper.extensions.item_entry_expansion.extra_fields')) {
            $query->with('artist', 'shopStock')->withCount('shopStock');
        }

        $categoryVisibleCheck = ItemCategory::visible(Auth::user() ?? null)->pluck('id', 'name')->toArray();
        // query where category is visible, or, no category and released
        $query->where(function ($query) use ($categoryVisibleCheck) {
            $query->whereIn('item_category_id', $categoryVisibleCheck)->orWhereNull('item_category_id');
        });
        $data = $request->only(['item_category_id', 'name', 'sort', 'artist', 'rarity_id']);
        if (isset($data['item_category_id'])) {
            if ($data['item_category_id'] == 'withoutOption') {
                $query->whereNull('item_category_id');
            } else {
                $query->where('item_category_id', $data['item_category_id']);
            }
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        if (isset($data['artist'])) {
            $query->where('artist_id', $data['artist']);
        }
        if (isset($data['rarity_id'])) {
            if ($data['rarity_id'] == 'withoutOption') {
                $query->whereNull('data->rarity_id');
            } else {
                $query->where('data->rarity_id', $data['rarity_id']);
            }
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
            }
        } else {
            $query->sortCategory();
        }

        return view('world.items', [
            'items'       => $query->orderBy('id')->paginate(20)->appends($request->query()),
            'categories'  => ['withoutOption' => 'Without Category'] + ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'shops'       => Shop::orderBy('sort', 'DESC')->get(),
            'artists'     => User::whereIn('id', Item::whereNotNull('artist_id')->pluck('artist_id')->toArray())->pluck('name', 'id')->toArray(),
            'rarities'    => ['withoutOption' => 'Without Rarity'] + Rarity::orderBy('rarities.sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows an individual item's page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItem($id) {
        $item = Item::where('id', $id)->released(Auth::user() ?? null)->with('category');

        if (config('lorekeeper.extensions.item_entry_expansion.extra_fields')) {
            $item->with('artist', 'shopStock')->withCount('shopStock');
        }

        $item = $item->first();

        if (!$item) {
            abort(404);
        }
        if ($item->category && !$item->category->is_visible) {
            if (Auth::check() ? !Auth::user()->isStaff : true) {
                abort(404);
            }
        }

        return view('world.item_page', [
            'item'        => $item,
        ]);
    }

    /**
     * Shows the character categories page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterCategories(Request $request) {
        $query = CharacterCategory::query()->with('sublist');

        $name = $request->get('name');
        if ($name) {
            $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('code', 'LIKE', '%'.$name.'%');
        }

        return view('world.character_categories', [
            'categories' => $query->visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->orderBy('id')->paginate(20)->appends($request->query()),
        ]);
    }
}
