<?php

namespace App\Services\Item;

use App\Models\Item\Item;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Services\CharacterManager;
use App\Services\InventoryManager;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class SlotService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Slot Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of slot type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @param mixed $tag
     *
     * @return array
     */
    public function getEditData($tag) {
        return [
            'rarities'  => ['0' => 'Select Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses' => ['0' => 'Select Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'subtypes'  => Subtype::orderBy('sort', 'DESC')->where('species_id', $this->getTagData($tag)['species_id'])->pluck('name', 'id')->toArray(),
            'isMyo'     => true,
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param object $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        // fetch data from DB, if there is no data then set to NULL instead
        $characterData['name'] = $tag->data['name'] ?? null;
        $characterData['species_id'] = isset($tag->data['species_id']) && $tag->data['species_id'] ? $tag->data['species_id'] : null;
        $characterData['subtype_ids'] = isset($tag->data['subtype_ids']) && $tag->data['subtype_ids'] ? $tag->data['subtype_ids'] : null;
        $characterData['rarity_id'] = isset($tag->data['rarity_id']) && $tag->data['rarity_id'] ? $tag->data['rarity_id'] : null;
        $characterData['description'] = isset($tag->data['description']) && $tag->data['description'] ? $tag->data['description'] : null;
        $characterData['parsed_description'] = parse($characterData['description']);
        $characterData['sale_value'] = $tag->data['sale_value'] ?? 0;
        // the switches hate true/false, need to convert boolean to binary
        if (isset($tag->data['is_sellable']) && $tag->data['is_sellable'] == 'true') {
            $characterData['is_sellable'] = 1;
        } else {
            $characterData['is_sellable'] = 0;
        }
        if (isset($tag->data['is_tradeable']) && $tag->data['is_tradeable'] == 'true') {
            $characterData['is_tradeable'] = 1;
        } else {
            $characterData['is_tradeable'] = 0;
        }
        if (isset($tag->data['is_giftable']) && $tag->data['is_giftable'] == 'true') {
            $characterData['is_giftable'] = 1;
        } else {
            $characterData['is_giftable'] = 0;
        }
        if (isset($tag->data['is_visible']) && $tag->data['is_visible'] == 'true') {
            $characterData['is_visible'] = 1;
        } else {
            $characterData['is_visible'] = 0;
        }

        return $characterData;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for DB storage.
     *
     * @param object $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        // put inputs into an array to transfer to the DB
        $characterData['name'] = $data['name'] ?? null;
        $characterData['species_id'] = isset($data['species_id']) && $data['species_id'] ? $data['species_id'] : null;
        $characterData['subtype_ids'] = isset($data['subtype_ids']) && $data['subtype_ids'] ? $data['subtype_ids'] : null;
        $characterData['rarity_id'] = isset($data['rarity_id']) && $data['rarity_id'] ? $data['rarity_id'] : null;
        $characterData['description'] = isset($data['description']) && $data['description'] ? $data['description'] : null;
        $characterData['parsed_description'] = parse($characterData['description']);
        $characterData['sale_value'] = $data['sale_value'] ?? 0;
        // if the switch was toggled, set true, if null, set false
        $characterData['is_sellable'] = isset($data['is_sellable']);
        $characterData['is_tradeable'] = isset($data['is_tradeable']);
        $characterData['is_giftable'] = isset($data['is_giftable']);
        $characterData['is_visible'] = isset($data['is_visible']);

        DB::beginTransaction();

        try {
            // get characterData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => $characterData]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param \App\Models\User\UserItem $stacks
     * @param User                      $user
     * @param array                     $data
     *
     * @return bool
     */
    public function act($stacks, $user, $data) {
        DB::beginTransaction();

        try {
            foreach ($stacks as $key=> $stack) {
                // We don't want to let anyone who isn't the owner of the slot to use it,
                // so do some validation...
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }

                // Next, try to delete the tag item. If successful, we can start distributing rewards.
                if ((new InventoryManager)->debitStack($stack->user, 'Slot Used', ['data' => ''], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        // fill an array with the DB contents
                        $characterData = $stack->item->tag('slot')->data;
                        // set user who is opening the item
                        $characterData['user_id'] = $user->id;
                        // other vital data that is default
                        $characterData['name'] ??= 'Slot';
                        $characterData['transferrable_at'] = null;
                        $characterData['is_myo_slot'] = 1;
                        // this uses your default MYO slot image from the CharacterManager
                        // see wiki page for documentation on adding a default image switch
                        $characterData['use_cropper'] = 0;
                        $characterData['x0'] = null;
                        $characterData['x1'] = null;
                        $characterData['y0'] = null;
                        $characterData['y1'] = null;
                        $characterData['image'] = null;
                        $characterData['thumbnail'] = null;
                        $characterData['artist_id'][0] = null;
                        $characterData['artist_url'][0] = null;
                        $characterData['designer_id'][0] = null;
                        $characterData['designer_url'][0] = null;
                        $characterData['feature_id'][0] = null;
                        $characterData['feature_data'][0] = null;

                        // DB has 'true' and 'false' as strings, so need to set them to true/null
                        if ($stack->item->tag('slot')->data['is_sellable'] == 'true') {
                            $characterData['is_sellable'] = true;
                        } else {
                            $characterData['is_sellable'] = null;
                        }
                        if ($stack->item->tag('slot')->data['is_tradeable'] == 'true') {
                            $characterData['is_tradeable'] = true;
                        } else {
                            $characterData['is_tradeable'] = null;
                        }
                        if ($stack->item->tag('slot')->data['is_giftable'] == 'true') {
                            $characterData['is_giftable'] = true;
                        } else {
                            $characterData['is_giftable'] = null;
                        }
                        if ($stack->item->tag('slot')->data['is_visible'] == 'true') {
                            $characterData['is_visible'] = true;
                        } else {
                            $characterData['is_visible'] = null;
                        }

                        // Distribute user rewards
                        $charService = new CharacterManager;
                        if ($character = $charService->createCharacter($characterData, $user, true)) {
                            flash('<a href="'.$character->url.'">MYO slot</a> created successfully.')->success();
                        } else {
                            foreach ($charService->errors()->getMessages()['error'] as $error) {
                                flash($error)->error();
                            }
                            throw new \Exception('Failed to use slot.');
                        }
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
