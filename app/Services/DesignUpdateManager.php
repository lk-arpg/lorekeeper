<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterFeature;
use App\Models\Character\CharacterImage;
use App\Models\Character\CharacterImageSubtype;
use App\Models\Currency\Currency;
use App\Models\Feature\Feature;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Models\User\UserItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class DesignUpdateManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Design Update Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of design update data.
    |
    */

    /**
     * Creates a character design update request (or a MYO design approval request).
     *
     * @param Character $character
     * @param User      $user
     *
     * @return bool|CharacterDesignUpdate
     */
    public function createDesignUpdateRequest($character, $user) {
        DB::beginTransaction();

        try {
            if ($character->user_id != $user->id) {
                throw new \Exception('You do not own this character.');
            }
            if (CharacterDesignUpdate::where('character_id', $character->id)->active()->exists()) {
                throw new \Exception('This '.($character->is_myo_slot ? 'MYO slot' : 'character').' already has an existing request. Please update that one, or delete it before creating a new one.');
            }
            if (!$character->isAvailable) {
                throw new \Exception('This '.($character->is_myo_slot ? 'MYO slot' : 'character').' is currently in an open trade or transfer. Please cancel the trade or transfer before creating a design update.');
            }

            $data = [
                'user_id'       => $user->id,
                'character_id'  => $character->id,
                'status'        => 'Draft',
                'hash'          => randomString(10),
                'fullsize_hash' => randomString(15),
                'update_type'   => $character->is_myo_slot ? 'MYO' : 'Character',

                // Set some data based on the character's existing stats
                'rarity_id'     => $character->image->rarity_id,
                'species_id'    => $character->image->species_id,
                'subtype_ids'   => $character->image->subtypes()->pluck('subtype_id'),
            ];

            $request = CharacterDesignUpdate::create($data);

            // If the character is not a MYO slot, make a copy of the previous image's traits
            // as presumably, we will not want to make major modifications to them.
            // This is skipped for MYO slots as it complicates things later on - we don't want
            // users to edit compulsory traits, so we'll only add them when the design is approved.
            if (!$character->is_myo_slot) {
                foreach ($character->image->features as $feature) {
                    $request->features()->create([
                        'character_image_id' => $request->id,
                        'character_type'     => 'Update',
                        'feature_id'         => $feature->feature_id,
                        'data'               => $feature->data,
                    ]);
                }
            }

            return $this->commitReturn($request);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Saves the comment section of a character design update request.
     *
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     *
     * @return bool
     */
    public function saveRequestComment($data, $request) {
        DB::beginTransaction();

        try {
            // Update the comments section
            $request->comments = (isset($data['comments']) && $data['comments']) ? $data['comments'] : null;
            $request->has_comments = 1;
            $request->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Saves the image upload section of a character design update request.
     *
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     * @param bool                  $isAdmin
     *
     * @return bool
     */
    public function saveRequestImage($data, $request, $isAdmin = false) {
        DB::beginTransaction();

        try {
            // Require an image to be uploaded the first time, but if an image already exists, allow user to update the other details
            if (!$isAdmin && !isset($data['image']) && !file_exists($request->imagePath.'/'.$request->imageFileName)) {
                throw new \Exception('Please upload a valid image.');
            }

            // Require a thumbnail to be uploaded the first time as well
            if (!file_exists($request->thumbnailPath.'/'.$request->thumbnailFileName)) {
                // If the crop dimensions are invalid...
                // The crop function resizes the thumbnail to fit, so we only need to check that it's not null
                if (!$isAdmin || ($isAdmin && isset($data['modify_thumbnail']))) {
                    if (isset($data['use_cropper']) && ($data['x0'] === null || $data['x1'] === null || $data['y0'] === null || $data['y1'] === null)) {
                        throw new \Exception('Invalid crop dimensions specified.');
                    }
                    if (!isset($data['use_cropper']) && !isset($data['thumbnail'])) {
                        throw new \Exception('Please upload a valid thumbnail or use the image cropper.');
                    }
                }
            }
            if (!$isAdmin || ($isAdmin && isset($data['modify_thumbnail']))) {
                $imageData = [];
                if (isset($data['use_cropper'])) {
                    $imageData = Arr::only($data, [
                        'use_cropper',
                        'x0', 'x1', 'y0', 'y1',
                    ]);
                    $imageData['use_cropper'] = isset($data['use_cropper']);
                }
                if (!$isAdmin && isset($data['image'])) {
                    if (config('lorekeeper.settings.store_masterlist_fullsizes')) {
                        if (config('lorekeeper.settings.masterlist_fullsizes_format') != null) {
                            $imageData['extension'] = config('lorekeeper.settings.masterlist_fullsizes_format');
                        } else {
                            $imageData['extension'] = $data['image']->getClientOriginalExtension();
                        }
                    } elseif (config('lorekeeper.settings.masterlist_image_format') != null) {
                        $imageData['extension'] = config('lorekeeper.settings.masterlist_image_format');
                    } else {
                        $imageData['extension'] = $data['image']->getClientOriginalExtension();
                    }
                    $imageData['has_image'] = true;
                }
                $request->update($imageData);
            }

            $request->designers()->delete();
            $request->artists()->delete();

            // Check that users with the specified id(s) exist on site
            foreach ($data['designer_id'] as $id) {
                if (isset($id) && $id) {
                    $user = User::find($id);
                    if (!$user) {
                        throw new \Exception('One or more designers is invalid.');
                    }
                }
            }
            foreach ($data['artist_id'] as $id) {
                if (isset($id) && $id) {
                    $user = $user = User::find($id);
                    if (!$user) {
                        throw new \Exception('One or more artists is invalid.');
                    }
                }
            }

            // Attach artists/designers
            foreach ($data['designer_id'] as $key => $id) {
                if ($id || $data['designer_url'][$key]) {
                    DB::table('character_image_creators')->insert([
                        'character_image_id' => $request->id,
                        'type'               => 'Designer',
                        'character_type'     => 'Update',
                        'url'                => $data['designer_url'][$key],
                        'user_id'            => $id,
                    ]);
                }
            }
            foreach ($data['artist_id'] as $key => $id) {
                if ($id || $data['artist_url'][$key]) {
                    DB::table('character_image_creators')->insert([
                        'character_image_id' => $request->id,
                        'type'               => 'Artist',
                        'character_type'     => 'Update',
                        'url'                => $data['artist_url'][$key],
                        'user_id'            => $id,
                    ]);
                }
            }

            // Save image
            if (!$isAdmin && isset($data['image'])) {
                $this->handleImage($data['image'], $request->imageDirectory, $request->imageFileName, null, isset($data['default_image']));
            }

            // Save thumbnail, if we have an image set
            if ((!$isAdmin) || ($isAdmin && isset($data['modify_thumbnail']))) {
                if (isset($data['use_cropper']) && (isset($data['image']) || ($isAdmin && isset($data['modify_thumbnail'])))) {
                    (new CharacterManager)->cropThumbnail(Arr::only($data, ['x0', 'x1', 'y0', 'y1']), $request);
                } elseif (isset($data['thumbnail'])) {
                    $this->handleImage($data['thumbnail'], $request->imageDirectory, $request->thumbnailFileName);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Saves the addons section of a character design update request.
     *
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     *
     * @return bool
     */
    public function saveRequestAddons($data, $request) {
        DB::beginTransaction();

        try {
            $requestData = $request->data;
            // First return any item stacks associated with this request
            if (isset($requestData['user']) && isset($requestData['user']['user_items'])) {
                foreach ($requestData['user']['user_items'] as $userItemId=> $quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                    }
                    if ($userItemRow->update_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                    }
                    $userItemRow->update_count -= $quantity;
                    $userItemRow->save();
                }
            }

            // Also return any currency associated with this request
            // This is stored in the data attribute
            $currencyManager = new CurrencyManager;
            if (isset($requestData['user']) && isset($requestData['user']['currencies'])) {
                foreach ($requestData['user']['currencies'] as $currencyId=> $quantity) {
                    $currencyManager->creditCurrency(null, $request->user, null, null, $currencyId, $quantity);
                }
            }
            if (isset($requestData['character']) && isset($requestData['character']['currencies'])) {
                foreach ($requestData['character']['currencies'] as $currencyId=> $quantity) {
                    $currencyManager->creditCurrency(null, $request->character, null, null, $currencyId, $quantity);
                }
            }

            $userAssets = createAssetsArray();
            $characterAssets = createAssetsArray(true);

            // Attach items. Technically, the user doesn't lose ownership of the item - we're just adding an additional holding field.
            // We're also not going to add logs as this might add unnecessary fluff to the logs and the items still belong to the user.
            // Perhaps later I'll add a way to locate items that are being held by updates/trades.
            if (isset($data['stack_id'])) {
                foreach ($data['stack_id'] as $stackId) {
                    $stack = UserItem::with('item')->find($stackId);
                    if (!$stack || $stack->user_id != $request->user_id) {
                        throw new \Exception('Invalid item selected.');
                    }
                    if (!isset($data['stack_quantity'][$stackId])) {
                        throw new \Exception('Invalid quantity selected.');
                    }
                    $stack->update_count += $data['stack_quantity'][$stackId];
                    $stack->save();

                    addAsset($userAssets, $stack, $data['stack_quantity'][$stackId]);
                }
            }

            // Attach currencies.
            if (isset($data['currency_id'])) {
                foreach ($data['currency_id'] as $holderKey=> $currencyIds) {
                    $holder = explode('-', $holderKey);
                    $holderType = $holder[0];
                    $holderId = $holder[1];

                    // The holder can be obtained from the request, but for sanity's sake we're going to perform a check
                    $holder = ($holderType == 'user' ? User::find($holderId) : Character::find($holderId));
                    if ($holderType == 'user' && $holder->id != $request->user_id) {
                        throw new \Exception('Error attaching currencies to this request. (1)');
                    } elseif ($holderType == 'character' && $holder->id != $request->character_id) {
                        throw new \Exception('Error attaching currencies to this request. (2)');
                    }

                    foreach ($currencyIds as $key=> $currencyId) {
                        $currency = Currency::find($currencyId);
                        if (!$currency) {
                            throw new \Exception('Invalid currency selected.');
                        }
                        if (!$currencyManager->debitCurrency($holder, null, null, null, $currency, $data['currency_quantity'][$holderKey][$key])) {
                            throw new \Exception('Invalid currency/quantity selected.');
                        }

                        if ($holderType == 'user') {
                            addAsset($userAssets, $currency, $data['currency_quantity'][$holderKey][$key]);
                        } else {
                            addAsset($characterAssets, $currency, $data['currency_quantity'][$holderKey][$key]);
                        }
                    }
                }
            }

            $request->has_addons = 1;
            $request->data = [
                'user'      => Arr::only(getDataReadyAssets($userAssets), ['user_items', 'currencies']),
                'character' => Arr::only(getDataReadyAssets($characterAssets), ['currencies']),
            ];
            $request->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Saves the character features (traits) section of a character design update request.
     *
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     *
     * @return bool
     */
    public function saveRequestFeatures($data, $request) {
        DB::beginTransaction();

        try {
            if (!($request->character->is_myo_slot && $request->character->image->species_id) && !isset($data['species_id'])) {
                throw new \Exception('Please select a species.');
            }
            if (!($request->character->is_myo_slot && $request->character->image->rarity_id) && !isset($data['rarity_id'])) {
                throw new \Exception('Please select a rarity.');
            }

            $rarity = ($request->character->is_myo_slot && $request->character->image->rarity_id) ? $request->character->image->rarity : Rarity::find($data['rarity_id']);
            $species = ($request->character->is_myo_slot && $request->character->image->species_id) ? $request->character->image->species : Species::find($data['species_id']);

            if (($request->character->is_myo_slot && count($request->character->image->subtypes))) {
                $subtypes = $request->character->image->subtypes()->pluck('subtype_id')->toArray();
            } else {
                if (isset($data['subtype_ids']) && $data['subtype_ids']) {
                    if (count($data['subtype_ids']) > config('lorekeeper.extensions.multiple_subtype_limit')) {
                        throw new \Exception('Too many subtypes selected.');
                    }
                    $subtypes = $data['subtype_ids'];
                    foreach ($data['subtype_ids'] as $subtypeId) {
                        $subtype = Subtype::find($subtypeId);
                        if (!$subtype) {
                            throw new \Exception('Invalid subtype selected.');
                        }
                        if ($subtype && $subtype->species_id != $species->id) {
                            throw new \Exception('Subtype does not match the species.');
                        }
                    }
                }
            }

            if (!$rarity) {
                throw new \Exception('Invalid rarity selected.');
            }
            if (!$species) {
                throw new \Exception('Invalid species selected.');
            }

            // Clear old features
            $request->features()->delete();

            // Attach features
            // We'll do the compulsory ones at the time of approval.

            $features = Feature::whereIn('id', $data['feature_id'])->with('rarity')->get()->keyBy('id');

            foreach ($data['feature_id'] as $key => $featureId) {
                if (!$featureId) {
                    continue;
                }

                // Skip the feature if the rarity is too high.
                // Comment out this check if rarities should have more berth for traits choice.
                // if($features[$featureId]->rarity->sort > $rarity->sort) continue;

                // Skip the feature if it's not the correct species.
                if ($features[$featureId]->species_id && $features[$featureId]->species_id != $species->id) {
                    continue;
                }

                $feature = CharacterFeature::create(['character_image_id' => $request->id, 'feature_id' => $featureId, 'data' => $data['feature_data'][$key], 'character_type' => 'Update']);
            }

            // Update other stats
            $request->species_id = $species->id;
            $request->rarity_id = $rarity->id;
            $request->subtype_ids = $subtypes ?? null;
            $request->has_features = 1;
            $request->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Submit a character design update request to the approval queue.
     *
     * @param CharacterDesignUpdate $request
     *
     * @return bool
     */
    public function submitRequest($request) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Draft') {
                throw new \Exception('This request cannot be resubmitted to the queue.');
            }

            // Recheck and set update type, as insurance/in case of pre-existing drafts
            if ($request->character->is_myo_slot) {
                $request->update_type = 'MYO';
            } else {
                $request->update_type = 'Character';
            }
            // We've done validation and all section by section,
            // so it's safe to simply set the status to Pending here
            $request->status = 'Pending';
            if (!$request->submitted_at) {
                $request->submitted_at = Carbon::now();
            }
            $request->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approves a character design update request and processes it.
     *
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     * @param User                  $user
     *
     * @return bool
     */
    public function approveRequest($data, $request, $user) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Pending') {
                throw new \Exception('This request cannot be processed.');
            }
            if (!isset($data['character_category_id'])) {
                throw new \Exception('Please select a character category.');
            }
            if (!isset($data['number'])) {
                throw new \Exception('Please enter a character number.');
            }
            if (!isset($data['slug']) || Character::where('slug', $data['slug'])->where('id', '!=', $request->character_id)->exists()) {
                throw new \Exception('Please enter a unique character code.');
            }

            if (!$this->logAdminAction($user, 'Approved Design Update', 'Approved design update <a href="'.$request->url.'">#'.$request->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            // Remove any added items/currency
            // Currency has already been removed, so no action required
            // However logs need to be added for each of these
            $requestData = $request->data;
            $inventoryManager = new InventoryManager;
            if (isset($requestData['user']) && isset($requestData['user']['user_items'])) {
                $stacks = $requestData['user']['user_items'];
                foreach ($requestData['user']['user_items'] as $userItemId=> $quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                    }
                    if ($userItemRow->update_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                    }
                    $userItemRow->update_count -= $quantity;
                    $userItemRow->save();
                }

                $staff = $user;
                foreach ($stacks as $stackId=> $quantity) {
                    $stack = UserItem::find($stackId);
                    $user = User::find($request->user_id);
                    if (!$inventoryManager->debitStack($user, $request->character->is_myo_slot ? 'MYO Design Approved' : 'Character Design Updated', ['data' => 'Item used in '.($request->character->is_myo_slot ? 'MYO design approval' : 'Character design update').' (<a href="'.$request->url.'">#'.$request->id.'</a>)'], $stack, $quantity)) {
                        throw new \Exception('Failed to create log for item stack.');
                    }
                }
                $user = $staff;
            }
            $currencyManager = new CurrencyManager;
            if (isset($requestData['user']['currencies']) && $requestData['user']['currencies']) {
                foreach ($requestData['user']['currencies'] as $currencyId=> $quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currencyManager->createLog(
                        $request->user_id,
                        'User',
                        null,
                        null,
                        $request->character->is_myo_slot ? 'MYO Design Approved' : 'Character Design Updated',
                        'Used in '.($request->character->is_myo_slot ? 'MYO design approval' : 'character design update').' (<a href="'.$request->url.'">#'.$request->id.'</a>)',
                        $currencyId,
                        $quantity
                    )) {
                        throw new \Exception('Failed to create log for user currency.');
                    }
                }
            }
            if (isset($requestData['character']['currencies']) && $requestData['character']['currencies']) {
                foreach ($requestData['character']['currencies'] as $currencyId=> $quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currencyManager->createLog(
                        $request->character_id,
                        'Character',
                        null,
                        null,
                        $request->character->is_myo_slot ? 'MYO Design Approved' : 'Character Design Updated',
                        'Used in '.($request->character->is_myo_slot ? 'MYO design approval' : 'character design update').' (<a href="'.$request->url.'">#'.$request->id.'</a>)',
                        $currencyId,
                        $quantity
                    )) {
                        throw new \Exception('Failed to create log for character currency.');
                    }
                }
            }

            // Create a new image with the request data
            $image = CharacterImage::create([
                'character_id'       => $request->character_id,
                'is_visible'         => 1,
                'hash'               => $request->hash,
                'fullsize_hash'      => $request->fullsize_hash ? $request->fullsize_hash : randomString(15),
                'extension'          => config('lorekeeper.settings.masterlist_image_format') != null ? config('lorekeeper.settings.masterlist_image_format') : $request->extension,
                'fullsize_extension' => config('lorekeeper.settings.masterlist_fullsizes_format') != null ? config('lorekeeper.settings.masterlist_fullsizes_format') : $request->extension,
                'use_cropper'        => $request->use_cropper,
                'x0'                 => $request->x0,
                'x1'                 => $request->x1,
                'y0'                 => $request->y0,
                'y1'                 => $request->y1,
                'species_id'         => $request->species_id,
                'rarity_id'          => $request->rarity_id,
                'sort'               => 0,
            ]);

            // do subtype stuff
            if ($request->character->is_myo_slot && count($request->character->image->subtypes)) {
                foreach ($request->character->image->subtypes as $subtype) {
                    CharacterImageSubtype::create([
                        'character_image_id' => $image->id,
                        'subtype_id'         => $subtype->subtype_id,
                    ]);
                }
            } elseif ($request->subtype_ids) {
                foreach ($request->subtypes() as $subtypeId) {
                    CharacterImageSubtype::create([
                        'character_image_id' => $image->id,
                        'subtype_id'         => $subtypeId,
                    ]);
                }
            }

            // Shift the image credits over to the new image
            $request->designers()->update(['character_type' => 'Character', 'character_image_id' => $image->id]);
            $request->artists()->update(['character_type' => 'Character', 'character_image_id' => $image->id]);

            // Add the compulsory features
            if ($request->character->is_myo_slot) {
                foreach ($request->character->image->features as $feature) {
                    CharacterFeature::create(['character_image_id' => $image->id, 'feature_id' => $feature->feature_id, 'data' => $feature->data, 'character_type' => 'Character']);
                }
            }

            // Shift the image features over to the new image
            $request->rawFeatures()->update(['character_image_id' => $image->id, 'character_type' => 'Character']);

            // Make the image directory if it doesn't exist
            if (!file_exists($image->imagePath)) {
                // Create the directory.
                if (!mkdir($image->imagePath, 0755, true)) {
                    $this->setError('error', 'Failed to create image directory.');

                    return false;
                }
                chmod($image->imagePath, 0755);
            }

            // Move the image file to the new image
            File::move($request->imagePath.'/'.$request->imageFileName, $image->imagePath.'/'.$image->imageFileName);
            // Process and save the image
            (new CharacterManager)->processImage($image);

            // The thumbnail is already generated, so it can just be moved without processing
            File::move($request->thumbnailPath.'/'.$request->thumbnailFileName, $image->thumbnailPath.'/'.$image->thumbnailFileName);

            // Set character data and other info such as cooldown time, resell cost and terms etc.
            // since those might be updated with the new design update
            if (isset($data['transferrable_at'])) {
                $request->character->transferrable_at = $data['transferrable_at'];
            }
            $request->character->character_category_id = $data['character_category_id'];
            $request->character->number = $data['number'];
            $request->character->slug = $data['slug'];
            $request->character->rarity_id = $request->rarity_id;

            $request->character->description = $data['description'];
            $request->character->parsed_description = parse($data['description']);

            $request->character->is_sellable = isset($data['is_sellable']);
            $request->character->is_tradeable = isset($data['is_tradeable']);
            $request->character->is_giftable = isset($data['is_giftable']);
            $request->character->sale_value = $data['sale_value'] ?? 0;

            // Invalidate old image if desired
            if (isset($data['invalidate_old'])) {
                $request->character->image->is_valid = 0;
                $request->character->image->save();
            }

            // Note old image to delete it
            if (config('lorekeeper.extensions.remove_myo_image') && $request->character->is_myo_slot && $data['remove_myo_image'] == 2) {
                $oldImage = $request->character->image;
            }

            // Hide the MYO placeholder image if desired
            if (config('lorekeeper.extensions.remove_myo_image') && $request->character->is_myo_slot && $data['remove_myo_image'] == 1) {
                $request->character->image->is_visible = 0;
                $request->character->image->save();
            }

            // Set new image if desired
            if (isset($data['set_active'])) {
                $request->character->character_image_id = $image->id;
            }

            // Final recheck and setting of update type, as insurance
            if ($request->character->is_myo_slot) {
                $request->update_type = 'MYO';
            } else {
                $request->update_type = 'Character';
            }
            $request->save();

            // Add a log for the character and user
            (new CharacterManager)->createLog($user->id, null, $request->character->user_id, $request->character->user->url, $request->character->id, $request->update_type == 'MYO' ? 'MYO Design Approved' : 'Character Design Updated', '[#'.$image->id.']', 'character');
            (new CharacterManager)->createLog($user->id, null, $request->character->user_id, $request->character->user->url, $request->character->id, $request->update_type == 'MYO' ? 'MYO Design Approved' : 'Character Design Updated', '[#'.$image->id.']', 'user');

            // If this is for a MYO, set user's FTO status and the MYO status of the slot
            // and clear the character's name
            if ($request->character->is_myo_slot) {
                if (config('lorekeeper.settings.clear_myo_slot_name_on_approval')) {
                    $request->character->name = null;
                }
                $request->character->is_myo_slot = 0;
                $request->user->settings->is_fto = 0;
                $request->user->settings->save();

                // Delete the MYO placeholder image if desired
                if (config('lorekeeper.extensions.remove_myo_image') && $data['remove_myo_image'] == 2) {
                    $characterManager = new CharacterManager;
                    if (!$characterManager->deleteImage($oldImage, $user, true)) {
                        foreach ($characterManager->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }
                        throw new \Exception('Failed to delete MYO image.');
                    }
                }
            }
            $request->character->save();

            // Set status to approved
            $request->staff_id = $user->id;
            $request->status = 'Approved';
            $request->save();

            // Notify the user
            Notifications::create('DESIGN_APPROVED', $request->user, [
                'design_url'    => $request->url,
                'character_url' => $request->character->url,
                'name'          => $request->character->fullName,
            ]);

            // Notify bookmarkers
            $request->character->notifyBookmarkers('BOOKMARK_IMAGE');

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Rejects a character design update request and processes it.
     * Rejection can be a soft rejection (reopens the request so the user can edit it and resubmit)
     * or a hard rejection (takes the request out of the queue completely).
     *
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     * @param User                  $user
     * @param bool                  $forceReject
     * @param mixed                 $notification
     *
     * @return bool
     */
    public function rejectRequest($data, $request, $user, $forceReject = false, $notification = true) {
        DB::beginTransaction();

        try {
            if (!$forceReject && $request->status != 'Pending') {
                throw new \Exception('This request cannot be processed.');
            }

            if (!$this->logAdminAction($user, 'Rejected Design Update', 'Rejected design update <a href="'.$request->url.'">#'.$request->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            // This hard rejects the request - items/currency are returned to user
            // and the user will need to open a new request to resubmit.
            // Use when rejecting a request the user shouldn't have submitted at all.

            $requestData = $request->data;
            // Return all added items/currency
            if (isset($requestData['user']) && isset($requestData['user']['user_items'])) {
                foreach ($requestData['user']['user_items'] as $userItemId=> $quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                    }
                    if ($userItemRow->update_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                    }
                    $userItemRow->update_count -= $quantity;
                    $userItemRow->save();
                }
            }

            $currencyManager = new CurrencyManager;
            if (isset($requestData['user']['currencies']) && $requestData['user']['currencies']) {
                foreach ($requestData['user']['currencies'] as $currencyId=> $quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currency) {
                        throw new \Exception('Cannot return an invalid currency. ('.$currencyId.')');
                    }
                    if (!$currencyManager->creditCurrency(null, $request->user, null, null, $currency, $quantity)) {
                        throw new \Exception('Could not return currency to user. ('.$currencyId.')');
                    }
                }
            }
            if (isset($requestData['character']['currencies']) && $requestData['character']['currencies']) {
                foreach ($requestData['character']['currencies'] as $currencyId=> $quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currency) {
                        throw new \Exception('Cannot return an invalid currency. ('.$currencyId.')');
                    }
                    if (!$currencyManager->creditCurrency(null, $request->character, null, null, $currency, $quantity)) {
                        throw new \Exception('Could not return currency to character. ('.$currencyId.')');
                    }
                }
            }

            // Set staff comment and status
            $request->staff_id = $user->id;
            $request->staff_comments = $data['staff_comments'] ?? null;
            $request->status = 'Rejected';
            $request->save();

            if ($notification) {
                // Notify the user
                Notifications::create('DESIGN_REJECTED', $request->user, [
                    'design_url'    => $request->url,
                    'character_url' => $request->character->url,
                    'name'          => $request->character->fullName,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Cancels a character design update request.
     *
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     * @param User                  $user
     * @param bool                  $self
     *
     * @return bool
     */
    public function cancelRequest($data, $request, $user, $self = 0) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Pending') {
                throw new \Exception('This request cannot be processed.');
            }

            if (!$this->logAdminAction($user, 'Cancelled Design Update', 'Cancelled design update <a href="'.$request->url.'">#'.$request->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            // Soft removes the request from the queue -
            // it preserves all the data entered, but allows the staff member
            // to add a comment to it. Status is returned to Draft status.
            // Use when rejecting a request that just requires minor modifications to approve.

            if (!$self) {
                // Set staff comment if this is not a self-cancel
                $request->staff_id = $user->id;
                $request->staff_comments = $data['staff_comments'] ?? null;
                if (!isset($data['preserve_queue'])) {
                    $request->submitted_at = null;
                }
            } else {
                $request->submitted_at = null;
            }

            // Set status
            $request->status = 'Draft';
            $request->save();

            if (!$self) {
                // Notify the user if it is not being canceled by the user themself.
                // Note that an admin canceling their own will also not result in a notification
                Notifications::create('DESIGN_CANCELED', $request->user, [
                    'design_url'    => $request->url,
                    'character_url' => $request->character->url,
                    'name'          => $request->character->fullName,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a character design update request.
     *
     * @param CharacterDesignUpdate $request
     *
     * @return bool
     */
    public function deleteRequest($request) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Draft') {
                throw new \Exception('This request cannot be processed.');
            }

            // Deletes the request entirely, including images and etc.
            // This returns any attached items/currency
            // Characters with an open draft request cannot be transferred (due to attached items/currency),
            // so this is necessary to transfer a character

            $requestData = $request->data;
            // Return all added items/currency
            if (isset($requestData['user']) && isset($requestData['user']['user_items'])) {
                foreach ($requestData['user']['user_items'] as $userItemId=> $quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                    }
                    if ($userItemRow->update_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                    }
                    $userItemRow->update_count -= $quantity;
                    $userItemRow->save();
                }
            }

            $currencyManager = new CurrencyManager;
            if (isset($requestData['user']['currencies']) && $requestData['user']['currencies']) {
                foreach ($requestData['user']['currencies'] as $currencyId=> $quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currency) {
                        throw new \Exception('Cannot return an invalid currency. ('.$currencyId.')');
                    }
                    if (!$currencyManager->creditCurrency(null, $request->user, null, null, $currency, $quantity)) {
                        throw new \Exception('Could not return currency to user. ('.$currencyId.')');
                    }
                }
            }
            if (isset($requestData['character']['currencies']) && $requestData['character']['currencies']) {
                foreach ($requestData['character']['currencies'] as $currencyId=> $quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currency) {
                        throw new \Exception('Cannot return an invalid currency. ('.$currencyId.')');
                    }
                    if (!$currencyManager->creditCurrency(null, $request->character, null, null, $currency, $quantity)) {
                        throw new \Exception('Could not return currency to character. ('.$currencyId.')');
                    }
                }
            }

            // Delete the request
            $request->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Votes on a character design update request.
     *
     * @param string                $action
     * @param CharacterDesignUpdate $request
     * @param User                  $user
     *
     * @return bool
     */
    public function voteRequest($action, $request, $user) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Pending') {
                throw new \Exception('This request cannot be processed.');
            }
            if (!config('lorekeeper.extensions.design_update_voting')) {
                throw new \Exception('This extension is not currently enabled.');
            }

            switch ($action) {
                default:
                    flash('Invalid action.')->error();
                    break;
                case 'approve':
                    $vote = 2;
                    break;
                case 'reject':
                    $vote = 1;
                    break;
            }

            $voteData = (isset($request->vote_data) ? collect($request->vote_data, true) : collect([]));
            $voteData->get($user->id) ? $voteData->pull($user->id) : null;
            $voteData->put($user->id, $vote);
            $request->vote_data = $voteData;

            $request->save();

            if (!$this->logAdminAction($user, 'Voted on Design Update', 'Voted on design update <a href="'.$request->url.'">#'.$request->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
