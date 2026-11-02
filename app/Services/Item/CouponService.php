<?php

namespace App\Services\Item;

use App\Models\Item\Item;
use App\Services\Service;
use DB;

class CouponService extends Service {
    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [

        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param string $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        $couponData = [];
        $couponData['discount'] = $tag->data['discount'] ?? null;
        $couponData['infinite'] = $tag->data['infinite'] ?? null;

        return $couponData;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param string $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        DB::beginTransaction();

        try {
            if (!isset($data['infinite'])) {
                $data['infinite'] = 0;
            }

            $coupon['discount'] = $data['discount'];
            $coupon['infinite'] = $data['infinite'];
            $tag->update(['data' => $coupon]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
