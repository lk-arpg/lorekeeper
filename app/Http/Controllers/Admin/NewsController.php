<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller {
    /**
     * Shows the news index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.news.news', [
            'newses' => News::orderBy('updated_at', 'DESC')->paginate(20),
        ]);
    }

    /**
     * Shows the create news page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateNews() {
        return view('admin.news.create_edit_news', [
            'news' => new News,
        ]);
    }

    /**
     * Shows the edit news page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditNews($id) {
        $news = News::find($id);
        if (!$news) {
            abort(404);
        }

        return view('admin.news.create_edit_news', [
            'news' => $news,
        ]);
    }

    /**
     * Creates or edits a news page.
     *
     * @param App\Services\NewsService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditNews(Request $request, NewsService $service, $id = null) {
        $id ? $request->validate(News::$updateRules) : $request->validate(News::$createRules);
        $data = $request->only([
            'title', 'text', 'post_at', 'is_visible', 'bump', 'image', 'remove_image',
        ]);
        if ($id && $service->updateNews(News::find($id), $data, Auth::user())) {
            flash('News updated successfully.')->success();
        } elseif (!$id && $news = $service->createNews($data, Auth::user())) {
            flash('News created successfully.')->success();

            return redirect()->to('admin/news/edit/'.$news->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the news deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteNews($id) {
        $news = News::find($id);

        return view('admin.news._delete_news', [
            'news' => $news,
        ]);
    }

    /**
     * Deletes a news page.
     *
     * @param App\Services\NewsService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteNews(Request $request, NewsService $service, $id) {
        if ($id && $service->deleteNews(News::find($id))) {
            flash('News deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/news');
    }

    /**
     * Gets the news regeneration modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRegenNews($id) {
        $news = News::find($id);

        return view('admin.news._regen_news', [
            'news' => $news,
        ]);
    }

    /**
     * Regenerates a news page.
     *
     * @param App\Services\NewsService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRegenNews(Request $request, NewsService $service, $id) {
        if ($id && $service->regenNews(News::find($id))) {
            flash('News regenerated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/news/edit/'.$id);
    }
}
