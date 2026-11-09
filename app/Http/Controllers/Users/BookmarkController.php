<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Character\CharacterBookmark;
use App\Models\Character\CharacterBookmarkFolder;
use App\Services\BookmarkManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Bookmark Controller
    |--------------------------------------------------------------------------
    |
    | Handles the user's character bookmarks.
    |
    */

    /**
     * Shows the bookmarks page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBookmarks(Request $request) {
        $user = Auth::user();

        $folders = $user->bookmarkFolders()->withCount(['bookmarks' => function ($query) {
            $query->visible();
        }])->get();

        $raw = $request->get('folder');
        if (!isset($raw) || $raw == '' || $raw == 'all') {
            $currentFolderToken = 'all';
        } elseif ((int) $raw > 0 && $folders->firstWhere('id', (int) $raw)) {
            $currentFolderToken = (string) (int) $raw;
        } else {
            $currentFolderToken = 'uncategorized';
        }

        // custom order is per-folder; the all view falls back to a field sort
        $sort = $request->get('sort');
        if ($currentFolderToken == 'all') {
            $sort = $sort && $sort != 'sort_asc' ? $sort : 'number_desc';
        } else {
            $sort = $sort ?: 'sort_asc';
        }

        $query = CharacterBookmark::join('characters', 'character_bookmarks.character_id', '=', 'characters.id')
            ->join('character_images', 'characters.character_image_id', '=', 'character_images.id')
            ->with(['character.image.species', 'character.image.rarity', 'character.user'])->visible()
            ->where('character_bookmarks.user_id', $user->id)
            ->select('character_bookmarks.*');

        if ($currentFolderToken == 'uncategorized') {
            $query->whereNull('character_bookmarks.folder_id');
        } elseif ($currentFolderToken != 'all') {
            $query->where('character_bookmarks.folder_id', (int) $currentFolderToken);
        }

        switch ($sort) {
            case 'sort_asc':
                $query->orderBy('character_bookmarks.sort', 'ASC');
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
            case 'species_asc':
                $query->orderBy('character_images.species_id', 'ASC');
                break;
            case 'species_desc':
                $query->orderBy('character_images.species_id', 'DESC');
                break;
            case 'trade_asc':
                $query->orderBy('characters.is_trading', 'ASC');
                break;
            case 'trade_desc':
                $query->orderBy('characters.is_trading', 'DESC');
                break;
            case 'gift_art_asc':
                $query->orderBy('characters.is_gift_art_allowed', 'ASC');
                break;
            case 'gift_art_desc':
                $query->orderBy('characters.is_gift_art_allowed', 'DESC');
                break;
            case 'gift_write_asc':
                $query->orderBy('characters.is_gift_writing_allowed', 'ASC');
                break;
            case 'gift_write_desc':
                $query->orderBy('characters.is_gift_writing_allowed', 'DESC');
                break;
            default:
                $query->orderBy('characters.number', 'DESC');
        }

        $uncategorizedCount = $user->bookmarks()->visible()->whereNull('folder_id')->count();
        $allCount = $uncategorizedCount + $folders->sum('bookmarks_count');

        return view('account.bookmarks', [
            'bookmarks'          => $query->paginate(20)->appends($request->query()),
            'folders'            => $folders,
            'currentFolderToken' => $currentFolderToken,
            'currentSort'        => $sort,
            'allCount'           => $allCount,
            'uncategorizedCount' => $uncategorizedCount,
        ]);
    }

    /**
     * Shows the dedicated reorder page for a single folder.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getReorderBookmarks(Request $request) {
        $user = Auth::user();
        $folders = $user->bookmarkFolders()->get();

        // custom order is per-folder, so the all view has nothing to reorder
        $raw = $request->get('folder');
        if (!isset($raw) || $raw == '' || $raw == 'all') {
            return redirect('account/bookmarks');
        }
        $folder = (int) $raw > 0 ? $folders->firstWhere('id', (int) $raw) : null;

        $query = CharacterBookmark::with(['character.image.species', 'character.image.rarity'])->visible()
            ->where('user_id', $user->id)
            ->orderBy('sort', 'ASC');
        if ($folder) {
            $query->where('folder_id', $folder->id);
            $folderName = $folder->name;
            $folderToken = (string) $folder->id;
        } else {
            $query->whereNull('folder_id');
            $folderName = 'Uncategorized';
            $folderToken = 'uncategorized';
        }

        return view('account.bookmarks.reorder', [
            'bookmarks'   => $query->get(),
            'folderToken' => $folderToken,
            'folderName'  => $folderName,
        ]);
    }

    /**
     * Gets the bookmark creation modal.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateBookmark() {
        return view('account.bookmarks._create_edit_bookmark', [
            'bookmark' => new CharacterBookmark,
            'folders'  => Auth::user()->bookmarkFolders()->get(),
        ]);
    }

    /**
     * Gets the bookmark editing modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBookmark($id) {
        $bookmark = CharacterBookmark::with('character')->where('id', $id)->where('user_id', Auth::user()->id)->first();
        if (!$bookmark) {
            abort(404);
        }

        return view('account.bookmarks._create_edit_bookmark', [
            'bookmark' => $bookmark,
            'folders'  => Auth::user()->bookmarkFolders()->get(),
        ]);
    }

    /**
     * Creates or edits a bookmark.
     *
     * @param App\Services\BookmarkManager $service
     * @param int|null                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditBookmark(Request $request, BookmarkManager $service, $id = null) {
        $id ? $request->validate(CharacterBookmark::$updateRules) : $request->validate(CharacterBookmark::$createRules);
        $data = $request->only([
            'character_id', 'notify_on_trade_status', 'notify_on_gift_art_status', 'notify_on_gift_writing_status', 'notify_on_transfer', 'notify_on_image', 'comment', 'folder_id',
        ]);
        if ($id && $service->updateBookmark($data + ['bookmark_id' => $id], Auth::user())) {
            flash('Bookmark updated successfully.')->success();
        } elseif (!$id && $bookmark = $service->createBookmark($data, Auth::user())) {
            flash('Bookmark created successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the bookmark deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBookmark($id) {
        $bookmark = CharacterBookmark::with('character')->where('id', $id)->where('user_id', Auth::user()->id)->first();
        if (!$bookmark) {
            abort(404);
        }

        return view('account.bookmarks._delete_bookmark', [
            'bookmark' => $bookmark,
        ]);
    }

    /**
     * Deletes a bookmark.
     *
     * @param App\Services\BookmarkManager $service
     * @param int                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteBookmark(Request $request, BookmarkManager $service, $id) {
        if ($id && $service->deleteBookmark(['bookmark_id' => $id], Auth::user())) {
            flash('Bookmark deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Saves a manual bookmark sort order.
     *
     * @param App\Services\BookmarkManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortBookmarks(Request $request, BookmarkManager $service) {
        if ($service->sortBookmarks($request->get('sort'), Auth::user())) {
            flash('Bookmark order saved successfully.')->success();
            $folder = $request->get('folder');

            return redirect('account/bookmarks'.(isset($folder) ? '?folder='.urlencode($folder) : ''));
        }
        foreach ($service->errors()->getMessages()['error'] as $error) {
            flash($error)->error();
        }

        return redirect()->back();
    }

    /**
     * Gets the manage-folders modal.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getManageFolders() {
        return view('account.bookmarks._manage_folders', [
            'folders' => Auth::user()->bookmarkFolders()->withCount(['bookmarks' => function ($query) {
                $query->visible();
            }])->get(),
        ]);
    }

    /**
     * Creates a folder.
     *
     * @param App\Services\BookmarkManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateFolder(Request $request, BookmarkManager $service) {
        $request->validate(CharacterBookmarkFolder::$createRules);
        if ($service->createFolder($request->only(['name']), Auth::user())) {
            flash('Folder created successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Renames a folder.
     *
     * @param App\Services\BookmarkManager $service
     * @param int                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditFolder(Request $request, BookmarkManager $service, $id) {
        $request->validate(CharacterBookmarkFolder::$updateRules);
        if ($service->updateFolder($request->only(['name']) + ['folder_id' => $id], Auth::user())) {
            flash('Folder renamed successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Deletes a folder.
     *
     * @param App\Services\BookmarkManager $service
     * @param int                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFolder(Request $request, BookmarkManager $service, $id) {
        if ($service->deleteFolder(['folder_id' => $id], Auth::user())) {
            flash('Folder deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Saves a manual folder sort order.
     *
     * @param App\Services\BookmarkManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFolders(Request $request, BookmarkManager $service) {
        if ($service->sortFolders($request->get('sort'), Auth::user())) {
            flash('Folder order saved successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
