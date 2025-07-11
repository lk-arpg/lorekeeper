<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class NewsController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | News Controller
    |--------------------------------------------------------------------------
    |
    | Displays news posts and updates the user's news read status.
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct() {
        View::share('recentnews', News::visible()->orderBy('updated_at', 'DESC')->take(10)->get());
    }

    /**
     * Shows the news index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        if (Auth::check() && Auth::user()->is_news_unread) {
            Auth::user()->update(['is_news_unread' => 0]);
        }

        return view('news.index', ['newses' => News::visible()->orderBy('updated_at', 'DESC')->paginate(10)]);
    }

    /**
     * Shows a news post.
     *
     * @param int         $id
     * @param string|null $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNews($id, $slug = null) {
        $news = News::where('id', $id)->visible(Auth::user() ?? null)->first();
        if (!$news) {
            abort(404);
        }

        return view('news.news', ['news' => $news]);
    }
}
