<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
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
    public function getIndex(Request $request) {
        if (Auth::check() && Auth::user()->is_news_unread) {
            Auth::user()->update(['is_news_unread' => 0]);
        }

        $query = News::visible(Auth::user() ?? null);
        $data = $request->only(['title', 'sort']);
        if (isset($data['title'])) {
            $query->where('title', 'LIKE', '%'.$data['title'].'%');
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
                case 'bump':
                    $query->sortBump();
                    break;
                case 'bump-reverse':
                    $query->sortBump(true);
                    break;
            }
        } else {
            $query->sortBump(true);
        }

        return view('news.index', [
            'newses' => $query->paginate(10)->appends($request->query()),
        ]);
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
