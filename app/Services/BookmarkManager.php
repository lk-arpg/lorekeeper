<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Character\CharacterBookmark;
use App\Models\Character\CharacterBookmarkFolder;
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

            $id = (int) ($data['folder_id'] ?? null);
            $folderId = $id > 0 ? $this->findOwnedFolder($id, $user)->id : null;

            $bookmark = CharacterBookmark::create([
                'character_id'                  => $character->id,
                'user_id'                       => $user->id,
                'folder_id'                     => $folderId,
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

            $id = (int) ($data['folder_id'] ?? null);
            $folderId = $id > 0 ? $this->findOwnedFolder($id, $user)->id : null;

            $update = [
                'notify_on_trade_status'        => $data['notify_on_trade_status'] ?? 0,
                'notify_on_gift_art_status'     => $data['notify_on_gift_art_status'] ?? 0,
                'notify_on_gift_writing_status' => $data['notify_on_gift_writing_status'] ?? 0,
                'notify_on_transfer'            => $data['notify_on_transfer'] ?? 0,
                'notify_on_image'               => $data['notify_on_image'] ?? 0,
                'comment'                       => $data['comment'],
                'folder_id'                     => $folderId,
            ];

            if ($folderId != $bookmark->folder_id) {
                $update['sort'] = 0;
            }

            $bookmark->update($update);

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

    /**
     * Reorders bookmarks in the supplied order, top first (lowest sort).
     *
     * @param string                $data
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function sortBookmarks($data, $user) {
        DB::beginTransaction();

        try {
            $ids = array_filter(explode(',', (string) $data));
            if (!count($ids) || CharacterBookmark::whereIn('id', $ids)->where('user_id', $user->id)->count() != count($ids)) {
                throw new \Exception('Invalid bookmark included in sorting order.');
            }

            foreach (array_values($ids) as $key => $id) {
                CharacterBookmark::where('id', $id)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a bookmark folder.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|CharacterBookmarkFolder
     */
    public function createFolder($data, $user) {
        DB::beginTransaction();

        try {
            $name = trim($data['name'] ?? '');
            if ($name == '') {
                throw new \Exception('Folder name is required.');
            }
            if (CharacterBookmarkFolder::where('user_id', $user->id)->where('name', $name)->exists()) {
                throw new \Exception('You already have a folder with that name.');
            }

            CharacterBookmarkFolder::where('user_id', $user->id)->increment('sort');

            $folder = CharacterBookmarkFolder::create([
                'user_id' => $user->id,
                'name'    => $name,
                'sort'    => 0,
            ]);

            return $this->commitReturn($folder);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Renames a bookmark folder.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|CharacterBookmarkFolder
     */
    public function updateFolder($data, $user) {
        DB::beginTransaction();

        try {
            $folder = $this->findOwnedFolder($data['folder_id'] ?? null, $user);

            $name = trim($data['name'] ?? '');
            if ($name == '') {
                throw new \Exception('Folder name is required.');
            }
            if (CharacterBookmarkFolder::where('user_id', $user->id)->where('name', $name)->where('id', '!=', $folder->id)->exists()) {
                throw new \Exception('You already have a folder with that name.');
            }

            $folder->name = $name;
            $folder->save();

            return $this->commitReturn($folder);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a folder; its bookmarks revert to Uncategorized.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function deleteFolder($data, $user) {
        DB::beginTransaction();

        try {
            $folder = $this->findOwnedFolder($data['folder_id'] ?? null, $user);

            CharacterBookmark::where('user_id', $user->id)->where('folder_id', $folder->id)->update(['folder_id' => null, 'sort' => 0]);
            $folder->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Reorders the user's folders.
     *
     * @param string                $data
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function sortFolders($data, $user) {
        DB::beginTransaction();

        try {
            $ids = array_filter(explode(',', (string) $data));
            if (!count($ids) || CharacterBookmarkFolder::whereIn('id', $ids)->where('user_id', $user->id)->count() != count($ids)) {
                throw new \Exception('Invalid folder included in sorting order.');
            }

            foreach (array_values($ids) as $key => $id) {
                CharacterBookmarkFolder::where('id', $id)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Looks up a folder owned by the given user, or throws.
     *
     * @param mixed                 $id
     * @param \App\Models\User\User $user
     *
     * @return CharacterBookmarkFolder
     */
    private function findOwnedFolder($id, $user) {
        $folder = CharacterBookmarkFolder::where('id', $id)->where('user_id', $user->id)->first();
        if (!$folder) {
            throw new \Exception('Invalid folder selected.');
        }

        return $folder;
    }
}
