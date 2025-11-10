<?php

namespace App\Http\Controllers;

use App\Models\Sales\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class SalesController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | sales Controller
    |--------------------------------------------------------------------------
    |
    | Displays sales posts and updates the user's sales read status.
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct() {
        View::share('forsale', Sales::visible()->orderBy('updated_at', 'DESC')->where('is_open', 1)->get());
        View::share('recentsales', Sales::visible()->orderBy('updated_at', 'DESC')->take(10)->get());
    }

    /**
     * Shows the sales index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request) {
        if (Auth::check() && Auth::user()->is_sales_unread) {
            Auth::user()->update(['is_sales_unread' => 0]);
        }

        $query = Sales::visible();
        $data = $request->only(['title', 'is_open', 'sort']);
        if (isset($data['is_open']) && $data['is_open'] != 'none') {
            $query->where('is_open', $data['is_open']);
        }
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

        return view('sales.index', [
            'saleses' => $query->paginate(10)->appends($request->query()),
        ]);
    }

    /**
     * Shows a sales post.
     *
     * @param int         $id
     * @param string|null $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSales($id, $slug = null) {
        $sales = Sales::where('id', $id)->visible(Auth::user() ?? null)->first();
        if (!$sales) {
            abort(404);
        }

        return view('sales.sales', ['sales' => $sales]);
    }
}
