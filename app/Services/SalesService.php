<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Sales\Sales;
use App\Models\Sales\SalesCharacter;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Validator;

class SalesService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Sales Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of Sales posts.
    |
    */

    /**
     * Creates a Sales post.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|Sales
     */
    public function createSales($data, $user) {
        DB::beginTransaction();

        try {
            $data['parsed_text'] = parse($data['text']);
            $data['user_id'] = $user->id;
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['is_open'])) {
                $data['is_open'] = 0;
            }

            $sales = Sales::create($data);

            // The character identification comes in both the slug field and as character IDs
            // First, check if the characters are accessible to begin with.
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters do not exist.');
                }
            } else {
                $characters = [];
            }

            // Process entered character data
            if (isset($data['slug'])) {
                $this->processCharacters($sales, $data);
            }

            if ($sales->is_visible) {
                $this->alertUsers();
            }

            return $this->commitReturn($sales);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a Sales post.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $sales
     *
     * @return bool|Sales
     */
    public function updateSales($sales, $data, $user) {
        DB::beginTransaction();

        try {
            $data['parsed_text'] = parse($data['text']);
            $data['user_id'] = $user->id;
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['is_open'])) {
                $data['is_open'] = 0;
            }

            if (isset($data['bump']) && $data['is_visible'] == 1 && $data['bump'] == 1) {
                $this->alertUsers();
            }

            // The character identification comes in both the slug field and as character IDs
            // First, check if the characters are accessible to begin with.
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters do not exist.');
                }
            } else {
                $characters = [];
            }

            // Remove existing attached characters, then process entered character data
            $sales->characters()->delete();
            if (isset($data['slug'])) {
                $this->processCharacters($sales, $data);
            }

            $sales->update($data);

            return $this->commitReturn($sales);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a Sales post.
     *
     * @param mixed $sales
     *
     * @return bool
     */
    public function deleteSales($sales) {
        DB::beginTransaction();

        try {
            $sales->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates queued Sales posts to be visible and alert users when
     * they should be posted.
     *
     * @return bool
     */
    public function updateQueue() {
        $count = Sales::shouldBeVisible()->count();
        if ($count) {
            DB::beginTransaction();

            try {
                Sales::shouldBeVisible()->update(['is_visible' => 1]);
                $this->alertUsers();

                return $this->commitReturn(true);
            } catch (\Exception $e) {
                $this->setError('error', $e->getMessage());
            }

            return $this->rollbackReturn(false);
        }
    }

    /**
     * Processes sales data entered for characters.
     *
     * @param App\Models\Sales\Sales $sales
     * @param array                  $data
     *
     * @return bool
     */
    private function processCharacters($sales, $data) {
        foreach ($data['slug'] as $key=> $slug) {
            $character = Character::myo(0)->where('slug', $slug)->first();

            // Assemble data
            $charData[$key] = [];
            $charData[$key]['type'] = $data['sale_type'][$key];
            switch ($charData[$key]['type']) {
                case 'flatsale':
                    $charData[$key]['price'] = $data['price'][$key];
                    break;
                case 'auction':
                    $charData[$key]['starting_bid'] = $data['starting_bid'][$key];
                    $charData[$key]['min_increment'] = $data['min_increment'][$key];
                    if (isset($data['autobuy'][$key])) {
                        $charData[$key]['autobuy'] = $data['autobuy'][$key];
                    }
                    if (isset($data['end_point'][$key])) {
                        $charData[$key]['end_point'] = $data['end_point'][$key];
                    }
                    break;
                case 'ota':
                    if (isset($data['autobuy'][$key])) {
                        $charData[$key]['autobuy'] = $data['autobuy'][$key];
                    }
                    if (isset($data['end_point'][$key])) {
                        $charData[$key]['end_point'] = $data['end_point'][$key];
                    }
                    if (isset($data['minimum'][$key])) {
                        $charData[$key]['minimum'] = $data['minimum'][$key];
                    }
                    break;
                case 'xta':
                    if (isset($data['autobuy'][$key])) {
                        $charData[$key]['autobuy'] = $data['autobuy'][$key];
                    }
                    if (isset($data['end_point'][$key])) {
                        $charData[$key]['end_point'] = $data['end_point'][$key];
                    }
                    if (isset($data['minimum'][$key])) {
                        $charData[$key]['minimum'] = $data['minimum'][$key];
                    }
                    break;
                case 'flaffle':
                    $charData[$key]['price'] = $data['price'][$key];
                    break;
                case 'pwyw':
                    if (isset($data['minimum'][$key])) {
                        $charData[$key]['minimum'] = $data['minimum'][$key];
                    }
                    break;
            }

            // Validate data
            $validator = Validator::make($charData[$key], SalesCharacter::$rules);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            // Record data/attach the character to the sales post
            SalesCharacter::create([
                'character_id' => $character->id,
                'image_id'     => $data['image_id'][$key] ?? $character->image->id,
                'sales_id'     => $sales->id,
                'type'         => $charData[$key]['type'],
                'data'         => json_encode($charData[$key]),
                'description'  => $data['description'][$key] ?? null,
                'link'         => $data['link'][$key] ?? null,
                'is_open'      => $data['character_is_open'][$character->slug] ?? ($data['new_entry'][$key] ? 1 : 0),
            ]);
        }
    }

    /**
     * Updates the unread Sales flag for all users so that
     * the new Sales notification is displayed.
     *
     * @return bool
     */
    private function alertUsers() {
        User::query()->update(['is_sales_unread' => 1]);

        return true;
    }
}
