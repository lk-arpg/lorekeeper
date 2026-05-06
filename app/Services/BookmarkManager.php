<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Character\CharacterBookmark;
use Illuminate\Support\Facades\DB;

class BookmarkManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Bookmark Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation, modification and usage of character bookmarks.
    |
    */

    /**
     * Create a bookmark.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|CharacterBookmark
     */
    public function createBookmark($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['character_id'])) {
                throw new \Exception('Invalid character selected.');
            }

            $character = Character::where('id', $data['character_id'])->visible()->first();
            if (!$character) {
                throw new \Exception('Invalid character selected.');
            }

            if ($user->hasBookmarked($character)) {
                throw new \Exception('You have already bookmarked this character.');
            }

            $bookmark = CharacterBookmark::create([
                'character_id'                  => $character->id,
                'user_id'                       => $user->id,
                'sort'                          => 0,
                'notify_on_trade_status'        => $data['notify_on_trade_status'] ?? 0,
                'notify_on_gift_art_status'     => $data['notify_on_gift_art_status'] ?? 0,
                'notify_on_gift_writing_status' => $data['notify_on_gift_writing_status'] ?? 0,
                'notify_on_transfer'            => $data['notify_on_transfer'] ?? 0,
                'notify_on_image'               => $data['notify_on_image'] ?? 0,
                'comment'                       => $data['comment'],
            ]);

            return $this->commitReturn($bookmark);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a bookmark.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|CharacterBookmark
     */
    public function updateBookmark($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['bookmark_id'])) {
                throw new \Exception('Invalid bookmark selected.');
            }
            $bookmark = CharacterBookmark::with('character')->where('id', $data['bookmark_id'])->visible()->where('user_id', $user->id)->first();
            if (!$bookmark || !$bookmark->character->is_visible) {
                throw new \Exception('Invalid bookmark selected.');
            }

            $bookmark->update([
                'notify_on_trade_status'        => $data['notify_on_trade_status'] ?? 0,
                'notify_on_gift_art_status'     => $data['notify_on_gift_art_status'] ?? 0,
                'notify_on_gift_writing_status' => $data['notify_on_gift_writing_status'] ?? 0,
                'notify_on_transfer'            => $data['notify_on_transfer'] ?? 0,
                'notify_on_image'               => $data['notify_on_image'] ?? 0,
                'comment'                       => $data['comment'],
            ]);

            return $this->commitReturn($bookmark);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a bookmark.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function deleteBookmark($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['bookmark_id'])) {
                throw new \Exception('Invalid bookmark selected.');
            }
            $bookmark = CharacterBookmark::with('character')->where('id', $data['bookmark_id'])->visible()->where('user_id', $user->id)->first();
            if (!$bookmark || !$bookmark->character->is_visible) {
                throw new \Exception('Invalid bookmark selected.');
            }

            $bookmark->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes bookmarks associated with a character.
     * For use when a character is deleted.
     *
     * @param Character $character
     *
     * @return bool
     */
    public function deleteBookmarks($character) {
        DB::beginTransaction();

        try {
            CharacterBookmark::where('character_id', $character->id)->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
