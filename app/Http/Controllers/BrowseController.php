<?php

namespace App\Http\Controllers;

use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Character\CharacterCategory;
use App\Models\Character\CharacterImage;
use App\Models\Character\Sublist;
use App\Models\Feature\Feature;
use App\Models\Rank\Rank;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrowseController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Browse Controller
    |--------------------------------------------------------------------------
    |
    | Displays lists of users and characters.
    |
    */

    /**
     * Shows the user list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUsers(Request $request) {
        $query = User::visible()->with('primaryAlias')->join('ranks', 'users.rank_id', '=', 'ranks.id')->select('ranks.name AS rank_name', 'users.*');
        $sort = $request->only(['sort']);

        if ($request->get('name')) {
            $query->where(function ($query) use ($request) {
                $query->where('users.name', 'LIKE', '%'.$request->get('name').'%');
            });
        }
        if ($request->get('rank_id')) {
            $query->where('rank_id', $request->get('rank_id'));
        }

        switch ($sort['sort'] ?? null) {
            default:
                $query->orderBy('ranks.sort', 'DESC')->orderBy('name');
                break;
            case 'alpha':
                $query->orderBy('name');
                break;
            case 'alpha-reverse':
                $query->orderBy('name', 'DESC');
                break;
            case 'alias':
                $query->aliasSort();
                break;
            case 'alias-reverse':
                $query->aliasSort(true);
                break;
            case 'rank':
                $query->orderBy('ranks.sort', 'DESC')->orderBy('name');
                break;
            case 'newest':
                $query->orderBy('created_at', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'ASC');
                break;
        }

        return view('browse.users', [
            'users'             => $query->paginate(30)->appends($request->query()),
            'ranks'             => [0 => 'Any Rank'] + Rank::orderBy('ranks.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'blacklistLink'     => Settings::get('blacklist_link'),
            'deactivatedLink'   => Settings::get('deactivated_link'),
        ]);
    }

    /**
     * Shows the user deactivated.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeactivated(Request $request) {
        $canView = false;
        $key = Settings::get('deactivated_key');

        // First, check the display settings for the deactivated...
        $privacy = Settings::get('deactivated_privacy');
        if ($privacy == 3 ||
            (Auth::check() &&
            ($privacy == 2 ||
            ($privacy == 1 && Auth::user()->isStaff) ||
            ($privacy == 0 && Auth::user()->isAdmin)))) {
            // Next, check if the deactivated requires a key
            $canView = true;
            if ($key != '0' && ($request->get('key') != $key)) {
                $canView = false;
            }
        }

        return view('browse.deactivated', [
            'canView' => $canView,
            'privacy' => $privacy,
            'key'     => $key,
            'users'   => $canView ? User::where('is_deactivated', 1)->with('primaryAlias', 'settings')->orderBy('users.name')->paginate(30)->appends($request->query()) : null,
        ]);
    }

    /**
     * Shows the user blacklist.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBlacklist(Request $request) {
        $canView = false;
        $key = Settings::get('blacklist_key');

        // First, check the display settings for the blacklist...
        $privacy = Settings::get('blacklist_privacy');
        if ($privacy == 3 ||
            (Auth::check() &&
            ($privacy == 2 ||
            ($privacy == 1 && Auth::user()->isStaff) ||
            ($privacy == 0 && Auth::user()->isAdmin)))) {
            // Next, check if the blacklist requires a key
            $canView = true;
            if ($key != '0' && ($request->get('key') != $key)) {
                $canView = false;
            }
        }

        return view('browse.blacklist', [
            'canView' => $canView,
            'privacy' => $privacy,
            'key'     => $key,
            'users'   => $canView ? User::where('is_banned', 1)->with('primaryAlias', 'settings')->orderBy('users.name')->paginate(30)->appends($request->query()) : null,
        ]);
    }

    /**
     * Shows the character masterlist.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacters(Request $request) {
        $query = Character::with('user.rank', 'image.features', 'rarity', 'image.species', 'image.rarity')->myo(0);
        $imageQuery = CharacterImage::images(Auth::check() ? Auth::user() : null)->with('features', 'rarity', 'species', 'features');

        if ($sublists = Sublist::where('show_main', 0)->get()) {
            $subCategories = [];
            $subSpecies = [];
        }
        foreach ($sublists as $sublist) {
            $subCategories = array_merge($subCategories, $sublist->categories->pluck('id')->toArray());
            $subSpecies = array_merge($subSpecies, $sublist->species->pluck('id')->toArray());
        }

        $query->whereNotIn('character_category_id', $subCategories);
        $imageQuery->whereNotIn('species_id', $subSpecies);

        if ($request->get('name')) {
            $query->where(function ($query) use ($request) {
                $query->where('characters.name', 'LIKE', '%'.$request->get('name').'%')->orWhere('characters.slug', 'LIKE', '%'.$request->get('name').'%');
            });
        }
        if ($request->get('rarity_id')) {
            $query->where('rarity_id', $request->get('rarity_id'));
        }
        if ($request->get('character_category_id')) {
            $query->where('character_category_id', $request->get('character_category_id'));
        }

        if ($request->get('sale_value_min')) {
            $query->where('sale_value', '>=', $request->get('sale_value_min'));
        }
        if ($request->get('sale_value_max')) {
            $query->where('sale_value', '<=', $request->get('sale_value_max'));
        }

        if ($request->get('is_trading')) {
            $query->where('is_trading', 1);
        }
        if ($request->get('is_sellable')) {
            $query->where('is_sellable', 1);
        }
        if ($request->get('is_tradeable')) {
            $query->where('is_tradeable', 1);
        }
        if ($request->get('is_giftable')) {
            $query->where('is_giftable', 1);
        }

        if ($request->get('owner')) {
            $owner = User::find($request->get('owner'));
            if ($owner) {
                $query->where(function ($query) use ($owner) {
                    $query->where('user_id', $owner->id);
                });
            }
        }
        if ($request->get('owner_url')) {
            $ownerUrl = $request->get('owner_url');
            $query->where(function ($query) use ($ownerUrl) {
                $query->where('owner_url', 'LIKE', '%'.$ownerUrl.'%');
            });
        }

        // Search only main images
        if (!$request->get('search_images')) {
            $imageQuery->whereIn('id', $query->pluck('character_image_id')->toArray());
        }

        // Searching on image properties
        if ($request->get('species_id')) {
            $imageQuery->where('species_id', $request->get('species_id'));
        }
        if ($request->get('subtype_id')) {
            $imageQuery->where('subtype_id', $request->get('subtype_id'));
        }
        if ($request->get('feature_id')) {
            $featureIds = $request->get('feature_id');
            foreach ($featureIds as $featureId) {
                $imageQuery->whereHas('features', function ($query) use ($featureId) {
                    $query->where('feature_id', $featureId);
                });
            }
        }
        if ($request->get('artist')) {
            $artist = User::find($request->get('artist'));
            if ($artist) {
                $imageQuery->whereHas('artists', function ($query) use ($artist) {
                    $query->where('user_id', $artist->id);
                });
            }
        }
        if ($request->get('designer')) {
            $designer = User::find($request->get('designer'));
            if ($designer) {
                $imageQuery->whereHas('designers', function ($query) use ($designer) {
                    $query->where('user_id', $designer->id);
                });
            }
        }
        if ($request->get('artist_url')) {
            $artistUrl = $request->get('artist_url');
            $imageQuery->whereHas('artists', function ($query) use ($artistUrl) {
                $query->where('url', 'LIKE', '%'.$artistUrl.'%');
            });
        }
        if ($request->get('designer_url')) {
            $designerUrl = $request->get('designer_url');
            $imageQuery->whereHas('designers', function ($query) use ($designerUrl) {
                $query->where('url', 'LIKE', '%'.$designerUrl.'%');
            });
        }

        $query->whereIn('id', $imageQuery->pluck('character_id')->toArray());

        if ($request->get('is_gift_art_allowed')) {
            switch ($request->get('is_gift_art_allowed')) {
                case 1:
                    $query->where('is_gift_art_allowed', 1);
                    break;
                case 2:
                    $query->where('is_gift_art_allowed', 2);
                    break;
                case 3:
                    $query->where('is_gift_art_allowed', '>=', 1);
                    break;
            }
        }
        if ($request->get('is_gift_writing_allowed')) {
            switch ($request->get('is_gift_writing_allowed')) {
                case 1:
                    $query->where('is_gift_writing_allowed', 1);
                    break;
                case 2:
                    $query->where('is_gift_writing_allowed', 2);
                    break;
                case 3:
                    $query->where('is_gift_writing_allowed', '>=', 1);
                    break;
            }
        }

        switch ($request->get('sort')) {
            default:
                $query->orderBy('characters.number', 'DESC');
                break;
            case 'number_desc':
                $query->orderBy('characters.number', 'DESC');
                break;
            case 'number_asc':
                $query->orderBy('characters.number', 'ASC');
                break;
            case 'id_desc':
                $query->orderBy('characters.id', 'DESC');
                break;
            case 'id_asc':
                $query->orderBy('characters.id', 'ASC');
                break;
            case 'sale_value_desc':
                $query->orderBy('characters.sale_value', 'DESC');
                break;
            case 'sale_value_asc':
                $query->orderBy('characters.sale_value', 'ASC');
                break;
        }

        if (!Auth::check() || !Auth::user()->hasPower('manage_characters')) {
            $query->visible();
        }

        return view('browse.masterlist', [
            'isMyo'       => false,
            'characters'  => $query->paginate(24)->appends($request->query()),
            'categories'  => [0 => 'Any Category'] + CharacterCategory::whereNotIn('id', $subCategories)->visible(Auth::check() ? Auth::user() : null)->orderBy('character_categories.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses'   => [0 => 'Any Species'] + Species::whereNotIn('id', $subSpecies)->visible(Auth::check() ? Auth::user() : null)->orderBy('specieses.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'subtypes'    => [0 => 'Any Subtype'] + Subtype::visible(Auth::check() ? Auth::user() : null)->orderBy('subtypes.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'rarities'    => [0 => 'Any Rarity'] + Rarity::orderBy('rarities.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'features'    => Feature::getDropdownItems(),
            'sublists'    => Sublist::orderBy('sort', 'DESC')->get(),
            'userOptions' => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the MYO slot masterlist.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMyos(Request $request) {
        $query = Character::with('user.rank', 'image.features', 'rarity', 'image.species', 'image.rarity')->myo(1);

        $imageQuery = CharacterImage::images(Auth::check() ? Auth::user() : null)->with('features', 'rarity', 'species', 'features');

        if ($request->get('name')) {
            $query->where(function ($query) use ($request) {
                $query->where('characters.name', 'LIKE', '%'.$request->get('name').'%')->orWhere('characters.slug', 'LIKE', '%'.$request->get('name').'%');
            });
        }
        if ($request->get('rarity_id')) {
            $query->where('rarity_id', $request->get('rarity_id'));
        }

        if ($request->get('sale_value_min')) {
            $query->where('sale_value', '>=', $request->get('sale_value_min'));
        }
        if ($request->get('sale_value_max')) {
            $query->where('sale_value', '<=', $request->get('sale_value_max'));
        }

        if ($request->get('is_trading')) {
            $query->where('is_trading', 1);
        }
        if ($request->get('is_sellable')) {
            $query->where('is_sellable', 1);
        }
        if ($request->get('is_tradeable')) {
            $query->where('is_tradeable', 1);
        }
        if ($request->get('is_giftable')) {
            $query->where('is_giftable', 1);
        }

        if ($request->get('owner')) {
            $owner = User::find($request->get('owner'));
            if ($owner) {
                $query->where(function ($query) use ($owner) {
                    $query->where('user_id', $owner->id);
                });
            }
        }
        if ($request->get('owner_url')) {
            $ownerUrl = $request->get('owner_url');
            $query->where(function ($query) use ($ownerUrl) {
                $query->where('owner_url', 'LIKE', '%'.$ownerUrl.'%');
            });
        }

        // Search only main images
        if (!$request->get('search_images')) {
            $imageQuery->whereIn('id', $query->pluck('character_image_id')->toArray());
        }

        // Searching on image properties
        if ($request->get('species_id')) {
            $imageQuery->where('species_id', $request->get('species_id'));
        }
        if ($request->get('artist')) {
            $artist = User::find($request->get('artist'));
            if ($artist) {
                $imageQuery->whereHas('artists', function ($query) use ($artist) {
                    $query->where('user_id', $artist->id);
                });
            }
        }
        if ($request->get('designer')) {
            $designer = User::find($request->get('designer'));
            if ($designer) {
                $imageQuery->whereHas('designers', function ($query) use ($designer) {
                    $query->where('user_id', $designer->id);
                });
            }
        }
        if ($request->get('artist_url')) {
            $artistUrl = $request->get('artist_url');
            $imageQuery->whereHas('artists', function ($query) use ($artistUrl) {
                $query->where('url', 'LIKE', '%'.$artistUrl.'%');
            });
        }
        if ($request->get('designer_url')) {
            $designerUrl = $request->get('designer_url');
            $imageQuery->whereHas('designers', function ($query) use ($designerUrl) {
                $query->where('url', 'LIKE', '%'.$designerUrl.'%');
            });
        }
        if ($request->get('feature_id')) {
            $featureIds = $request->get('feature_id');
            foreach ($featureIds as $featureId) {
                $imageQuery->whereHas('features', function ($query) use ($featureId) {
                    $query->where('feature_id', $featureId);
                });
            }
        }

        $query->whereIn('id', $imageQuery->pluck('character_id')->toArray());

        switch ($request->get('sort')) {
            default:
                $query->orderBy('characters.id', 'DESC');
                break;
            case 'id_desc':
                $query->orderBy('characters.id', 'DESC');
                break;
            case 'id_asc':
                $query->orderBy('characters.id', 'ASC');
                break;
            case 'sale_value_desc':
                $query->orderBy('characters.sale_value', 'DESC');
                break;
            case 'sale_value_asc':
                $query->orderBy('characters.sale_value', 'ASC');
                break;
        }

        if (!Auth::check() || !Auth::user()->hasPower('manage_characters')) {
            $query->visible();
        }

        return view('browse.myo_masterlist', [
            'isMyo'       => true,
            'slots'       => $query->paginate(24)->appends($request->query()),
            'specieses'   => [0 => 'Any Species'] + Species::visible(Auth::check() ? Auth::user() : null)->orderBy('specieses.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'rarities'    => [0 => 'Any Rarity'] + Rarity::orderBy('rarities.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'features'    => Feature::getDropdownItems(),
            'sublists'    => Sublist::orderBy('sort', 'DESC')->get(),
            'userOptions' => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the Sub masterlists.
     *
     * @param mixed $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSublist(Request $request, $key) {
        $query = Character::with('user.rank', 'image.features', 'rarity', 'image.species', 'image.rarity')->myo(0);
        $imageQuery = CharacterImage::with('features', 'rarity', 'species', 'features');

        $sublist = Sublist::where('key', $key)->first();
        if (!$sublist) {
            abort(404);
        }
        $subCategories = $sublist->categories->pluck('id')->toArray();
        $subSpecies = $sublist->species->pluck('id')->toArray();

        if ($subCategories) {
            $query->whereIn('character_category_id', $subCategories);
        }
        if ($subSpecies) {
            $imageQuery->whereIn('species_id', $subSpecies);
        }

        if ($request->get('name')) {
            $query->where(function ($query) use ($request) {
                $query->where('characters.name', 'LIKE', '%'.$request->get('name').'%')->orWhere('characters.slug', 'LIKE', '%'.$request->get('name').'%');
            });
        }
        if ($request->get('rarity_id')) {
            $query->where('rarity_id', $request->get('rarity_id'));
        }
        if ($request->get('character_category_id')) {
            $query->where('character_category_id', $request->get('character_category_id'));
        }

        if ($request->get('sale_value_min')) {
            $query->where('sale_value', '>=', $request->get('sale_value_min'));
        }
        if ($request->get('sale_value_max')) {
            $query->where('sale_value', '<=', $request->get('sale_value_max'));
        }

        if ($request->get('is_trading')) {
            $query->where('is_trading', 1);
        }
        if ($request->get('is_gift_art_allowed')) {
            switch ($request->get('is_gift_art_allowed')) {
                case 1:
                    $query->where('is_gift_art_allowed', 1);
                    break;
                case 2:
                    $query->where('is_gift_art_allowed', 2);
                    break;
                case 3:
                    $query->where('is_gift_art_allowed', '>=', 1);
                    break;
            }
        }
        if ($request->get('is_gift_writing_allowed')) {
            switch ($request->get('is_gift_writing_allowed')) {
                case 1:
                    $query->where('is_gift_writing_allowed', 1);
                    break;
                case 2:
                    $query->where('is_gift_writing_allowed', 2);
                    break;
                case 3:
                    $query->where('is_gift_writing_allowed', '>=', 1);
                    break;
            }
        }
        if ($request->get('is_sellable')) {
            $query->where('is_sellable', 1);
        }
        if ($request->get('is_tradeable')) {
            $query->where('is_tradeable', 1);
        }
        if ($request->get('is_giftable')) {
            $query->where('is_giftable', 1);
        }

        if ($request->get('owner')) {
            $owner = User::find($request->get('owner'));
            if ($owner) {
                $query->where(function ($query) use ($owner) {
                    $query->where('user_id', $owner->id);
                });
            }
        }
        if ($request->get('owner_url')) {
            $ownerUrl = $request->get('owner_url');
            $query->where(function ($query) use ($ownerUrl) {
                $query->where('owner_url', 'LIKE', '%'.$ownerUrl.'%');
            });
        }

        // Search only main images
        if (!$request->get('search_images')) {
            $imageQuery->whereIn('id', $query->pluck('character_image_id')->toArray());
        }

        // Searching on image properties
        if ($request->get('species_id')) {
            $imageQuery->where('species_id', $request->get('species_id'));
        }
        if ($request->get('subtype_id')) {
            $imageQuery->where('subtype_id', $request->get('subtype_id'));
        }
        if ($request->get('feature_id')) {
            $featureIds = $request->get('feature_id');
            foreach ($featureIds as $featureId) {
                $imageQuery->whereHas('features', function ($query) use ($featureId) {
                    $query->where('feature_id', $featureId);
                });
            }
        }
        if ($request->get('artist')) {
            $artist = User::find($request->get('artist'));
            if ($artist) {
                $imageQuery->whereHas('artists', function ($query) use ($artist) {
                    $query->where('user_id', $artist->id);
                });
            }
        }
        if ($request->get('designer')) {
            $designer = User::find($request->get('designer'));
            if ($designer) {
                $imageQuery->whereHas('designers', function ($query) use ($designer) {
                    $query->where('user_id', $designer->id);
                });
            }
        }
        if ($request->get('artist_url')) {
            $artistUrl = $request->get('artist_url');
            $imageQuery->whereHas('artists', function ($query) use ($artistUrl) {
                $query->where('url', 'LIKE', '%'.$artistUrl.'%');
            });
        }
        if ($request->get('designer_url')) {
            $designerUrl = $request->get('designer_url');
            $imageQuery->whereHas('designers', function ($query) use ($designerUrl) {
                $query->where('url', 'LIKE', '%'.$designerUrl.'%');
            });
        }

        $query->whereIn('id', $imageQuery->pluck('character_id')->toArray());

        switch ($request->get('sort')) {
            default:
                $query->orderBy('characters.number', 'DESC');
                break;
            case 'number_desc':
                $query->orderBy('characters.number', 'DESC');
                break;
            case 'number_asc':
                $query->orderBy('characters.number', 'ASC');
                break;
            case 'id_desc':
                $query->orderBy('characters.id', 'DESC');
                break;
            case 'id_asc':
                $query->orderBy('characters.id', 'ASC');
                break;
            case 'sale_value_desc':
                $query->orderBy('characters.sale_value', 'DESC');
                break;
            case 'sale_value_asc':
                $query->orderBy('characters.sale_value', 'ASC');
                break;
        }

        if (!Auth::check() || !Auth::user()->hasPower('manage_characters')) {
            $query->visible();
        }

        $subCategory = CharacterCategory::where('masterlist_sub_id', $sublist->id)->orderBy('character_categories.sort', 'DESC')->pluck('name', 'id')->toArray();
        if (!$subCategory) {
            $subCategory = CharacterCategory::visible(Auth::check() ? Auth::user() : null)->orderBy('character_categories.sort', 'DESC')->pluck('name', 'id')->toArray();
        }
        $subSpecies = Species::where('masterlist_sub_id', $sublist->id)->orderBy('specieses.sort', 'DESC')->pluck('name', 'id')->toArray();
        if (!$subSpecies) {
            $subSpecies = Species::visible(Auth::check() ? Auth::user() : null)->orderBy('specieses.sort', 'DESC')->pluck('name', 'id')->toArray();
        }

        return view('browse.sub_masterlist', [
            'isMyo'       => false,
            'characters'  => $query->paginate(24)->appends($request->query()),
            'categories'  => [0 => 'Any Category'] + $subCategory,
            'specieses'   => [0 => 'Any Species'] + $subSpecies,
            'subtypes'    => [0 => 'Any Subtype'] + Subtype::visible(Auth::check() ? Auth::user() : null)->orderBy('subtypes.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'rarities'    => [0 => 'Any Rarity'] + Rarity::orderBy('rarities.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'features'    => Feature::getDropdownItems(),
            'sublist'     => $sublist,
            'sublists'    => Sublist::orderBy('sort', 'DESC')->get(),
            'userOptions' => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }
}
