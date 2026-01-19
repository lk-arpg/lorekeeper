<?php

namespace App\Http\Controllers;

use App\Models\Queue\Queue;
use App\Models\Queue\QueueCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueuesController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Queues Controller
    |--------------------------------------------------------------------------
    |
    | Displays information about queues as entered in the admin panel.
    | Pages displayed by this controller form the Queues section of the site.
    |
    */

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('queues.index');
    }

    /**
     * Shows the queue categories page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueueCategories(Request $request) {
        $query = QueueCategory::display();
        $name = $request->get('name');
        if ($name) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        return view('queues.queue_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the queues page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueues(Request $request) {
        $query = Queue::splash()->active()->staffOnly(Auth::user() ?? null)->with('category');
        $data = $request->only(['queue_category_id', 'name', 'sort', 'open_queues']);
        if (isset($data['queue_category_id']) && $data['queue_category_id'] != 'none') {
            if ($data['queue_category_id'] == 'withoutOption') {
                $query->whereNull('queue_category_id');
            } else {
                $query->where('queue_category_id', $data['queue_category_id']);
            }
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        if (isset($data['open_queues'])) {
            switch ($data['open_queues']) {
                case 'open':
                    $query->open(true);
                    break;
                case 'closed':
                    $query->open(false);
                    break;
                case 'any':
                default:
                    // Don't filter
                    break;
            }
        }

        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
                case 'start':
                    $query->sortStart();
                    break;
                case 'start-reverse':
                    $query->sortStart(true);
                    break;
                case 'end':
                    $query->sortEnd();
                    break;
                case 'end-reverse':
                    $query->sortEnd(true);
                    break;
            }
        } else {
            $query->sortCategory();
        }

        return view('queues.queues', [
            'queues'     => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + ['withoutOption' => 'Without Category'] + QueueCategory::display()->orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows an individual queue.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueue(Request $request, $id) {
        $queue = Queue::active()->where('id', $id)->first();

        if (!$queue) {
            abort(404);
        }

        return view('queues.queue', [
            'queue' => $queue,
        ]);
    }

    /**
     * Shows the queue category with the given key.
     *
     * @param string $key
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueueIndexPage(Request $request, $key) {
        $category = QueueCategory::where('key', $key)->first();
        if (!$category) {
            abort(404);
        }

        return view('queues.index_page', [
            'category' => $category,
            'queues'   => $category->queues()->active()->staffOnly(Auth::user() ?? null)->paginate(20),
        ]);
    }
}
