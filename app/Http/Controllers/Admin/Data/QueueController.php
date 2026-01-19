<?php
namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Item\Item;
use App\Models\Queue\Queue;
use App\Models\Queue\QueueCategory;
use App\Models\Rank\Rank;
use App\Services\QueueService;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Queue Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of queue categories and queues.
    |
    */

    /**
     * Shows the queue category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.queues.queue_categories', [
            'categories' => QueueCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create queue category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateQueueCategory()
    {
        return view('admin.queues.create_edit_queue_category', [
            'category'      => new QueueCategory,
            'limit_periods' => [null => 'None', 'Hour' => 'Hour', 'Day' => 'Day', 'Week' => 'Week', 'Month' => 'Month', 'Year' => 'Year'],
        ]);
    }

    /**
     * Shows the edit queue category page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditQueueCategory($id)
    {
        $category = QueueCategory::find($id);
        if (! $category) {
            abort(404);
        }

        return view('admin.queues.create_edit_queue_category', [
            'category'      => $category,
            'limit_periods' => [null => 'None', 'Hour' => 'Hour', 'Day' => 'Day', 'Week' => 'Week', 'Month' => 'Month', 'Year' => 'Year'],
        ]);
    }

    /**
     * Creates or edits a queue category.
     *
     * @param App\Services\QueueService $service
     * @param int|null                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditQueueCategory(Request $request, QueueService $service, $id = null)
    {
        $id ? $request->validate(QueueCategory::$updateRules) : $request->validate(QueueCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'key', 'limit', 'limit_period', 'limit_concurrent','display'
        ]);
        if ($id && $service->updateQueueCategory(QueueCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        } elseif (! $id && $category = $service->createQueueCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();

            return redirect()->to('admin/data/queue-categories/edit/' . $category->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the queue category deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteQueueCategory($id)
    {
        $category = QueueCategory::find($id);

        return view('admin.queues._delete_queue_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes a queue category.
     *
     * @param App\Services\QueueService $service
     * @param int                        $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteQueueCategory(Request $request, QueueService $service, $id)
    {
        if ($id && $service->deleteQueueCategory(QueueCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/queue-categories');
    }

    /**
     * Sorts queue categories.
     *
     * @param App\Services\QueueService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortQueueCategory(Request $request, QueueService $service)
    {
        if ($service->sortQueueCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**********************************************************************************************

        QUEUES

    **********************************************************************************************/

    /**
     * Shows the queue category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueueIndex(Request $request)
    {
        $query = Queue::query();
        $data  = $request->only(['queue_category_id', 'name']);
        if (isset($data['queue_category_id']) && $data['queue_category_id'] != 'none') {
            $query->where('queue_category_id', $data['queue_category_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%' . $data['name'] . '%');
        }

        return view('admin.queues.queues', [
            'queues'     => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + QueueCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create queue page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateQueue()
    {

        $types  = config('lorekeeper.queue_types');
        $result = [];
        foreach ($types as $type => $typeData) {
            $result[$type] = $typeData['name'];
        }

        return view('admin.queues.create_edit_queue', [
            'queue'         => new Queue,
            'categories'    => ['none' => 'No category'] + QueueCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'types'         => $result,
            'limit_periods' => [null => 'None', 'Hour' => 'Hour', 'Day' => 'Day', 'Week' => 'Week', 'Month' => 'Month', 'Year' => 'Year'],
            'ranks'         => ['none' => 'All Staff with Submissions Power'] + Rank::pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit queue page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditQueue($id)
    {
        $queue = Queue::find($id);
        if (! $queue) {
            abort(404);
        }
        $types  = config('lorekeeper.queue_types');
        $result = [];
        foreach ($types as $type => $typeData) {
            $result[$type] = $typeData['name'];
        }

        return view('admin.queues.create_edit_queue', [
            'queue'         => $queue,
            'categories'    => ['none' => 'No category'] + QueueCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'types'         => $result,
            'item_limits'   => $queue->configSet('item_consume') ? Item::orderBy('name')->pluck('name', 'id') : [],
            'limit_periods' => [null => 'None', 'Hour' => 'Hour', 'Day' => 'Day', 'Week' => 'Week', 'Month' => 'Month', 'Year' => 'Year'],
            'ranks'         => Rank::pluck('name', 'id')->toArray(),
        ] + $queue->service->getEditData());
    }

    /**
     * Creates or edits a queue.
     *
     * @param App\Services\QueueService $service
     * @param int|null                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditQueue(Request $request, QueueService $service, $id = null)
    {
        $id ? $request->validate(Queue::$updateRules) : $request->validate(Queue::$createRules);
        $data = $request->only(['name', 'queue_category_id', 'staff_rank_id', 'summary', 'description', 'start_at', 'end_at', 'hide_before_start', 'hide_after_end', 'is_active', 'image', 'remove_image', 'prefix', 'hide_submissions', 'staff_only', 'form', 'queue_type', 'limit', 'limit_period', 'check_text', 'user_rewardable_type', 'user_rewardable_id', 'user_quantity', 'character_rewardable_type', 'character_rewardable_id', 'character_quantity', 'limit_concurrent'
        ]);
        if ($id && $service->updateQueue(Queue::find($id), $data, Auth::user())) {
            flash('Queue updated successfully.')->success();
        } elseif (! $id && $queue = $service->createQueue($data, Auth::user())) {
            flash('Queue created successfully.')->success();

            return redirect()->to('admin/data/queues/edit/' . $queue->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the queue deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteQueue($id)
    {
        $queue = Queue::find($id);

        return view('admin.queues._delete_queue', [
            'queue' => $queue,
        ]);
    }

    /**
     * Deletes a queue.
     *
     * @param App\Services\QueueService $service
     * @param int                        $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteQueue(Request $request, QueueService $service, $id)
    {
        if ($id && $service->deleteQueue(Queue::find($id))) {
            flash('Queue deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/queues');
    }

    /**
     * Edits a queue's type data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\QueueService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditType(Request $request, QueueService $service, $id)
    {
        $data = $request->all();
        if ($service->updateType(Queue::find($id), $data)) {
            flash('Queue type settings updated successfully.')->success();
            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }
}
