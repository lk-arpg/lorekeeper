<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Queue\Queue;
use App\Models\Queue\QueueCategory;
use App\Models\Raffle\Raffle;
use App\Models\Queue\QueueSubmission;
use App\Services\QueueSubmissionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueSubmissionController extends Controller {
    /**
     * Shows the submission index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubmissionIndex(Request $request, $status = null) {

        $submissions = QueueSubmission::with('queue')->where('status', $status ? ucfirst($status) : 'Pending')->whereNotNull('queue_id');
        $data = $request->only(['queue_category_id', 'sort']);
        if (isset($data['queue_category_id']) && $data['queue_category_id'] != 'none') {
            $submissions->whereHas('queue', function ($query) use ($data) {
                $query->where('queue_category_id', $data['queue_category_id']);
            });
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $submissions->sortNewest();
                    break;
                case 'oldest':
                    $submissions->sortOldest();
                    break;
            }
        } else {
            $submissions->sortOldest();
        }

        return view('admin.queues.submission_index', [
            'submissions' => $submissions->paginate(30)->appends($request->query()),
            'categories'  => ['none' => 'Any Category'] + QueueCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows a specific queue's submission index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueueSubmissionIndex(Request $request, $id, $status = null) {

        $queue = Queue::find($id);
        if ($queue->staff_rank_id && !in_array(Auth::user()->rank_id, $queue->staff_rank_id)) abort(404);
        
        $submissions = QueueSubmission::with('queue')->where('queue_id', $id)->where('status', $status ? ucfirst($status) : 'Pending');
        $data = $request->only(['sort']);
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $submissions->sortNewest();
                    break;
                case 'oldest':
                    $submissions->sortOldest();
                    break;
            }
        } else {
            $submissions->sortOldest();
        }

        return view('admin.queues.submission_index', [
            'queue' => $queue,
            'submissions' => $submissions->paginate(30)->appends($request->query()),
        ]);
    }

    /**
     * Shows the submission detail page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubmission($id) {
        $submission = QueueSubmission::whereNotNull('queue_id')->where('id', $id)->where('status', '!=', 'Draft')->first();
        $inventory = isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null;
        if (!$submission) {
            abort(404);
        }

        $queue = $submission->queue;

        return view('admin.queues.submission', [
            'submission'       => $submission,
            'queue'       => $queue,
            'inventory'        => $inventory,
            'itemsrow'         => Item::all()->keyBy('id'),
            'page'             => 'submission',
            'characters'       => Character::visible(Auth::user() ?? null)->myo(0)->orderBy('slug', 'DESC')->get()->pluck('fullName', 'slug')->toArray(),
        ] + $queue->service->getActData($queue) + ($submission->status == 'Pending' ? [
            'count'               => QueueSubmission::where('queue_id', $submission->queue_id)->where('status', 'Approved')->where('user_id', $submission->user_id)->count(),
        ] : []));
    }



    /**
     * Creates a new submission.
     *
     * @param App\Services\QueueSubmissionManager $service
     * @param int                            $id
     * @param string                         $action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSubmission(Request $request, QueueSubmissionManager $service, $id, $action) {

        $data = $request->all();
        if ($action == 'reject' && $service->rejectSubmission($request->only(['staff_comments']) + ['id' => $id], Auth::user())) {
            flash('Submission rejected successfully.')->success();
        } elseif ($action == 'cancel' && $service->cancelSubmission($request->only(['staff_comments']) + ['id' => $id], Auth::user())) {
            flash('Submission canceled successfully.')->success();

            return redirect()->to('admin/queue-submissions');
        } elseif ($action == 'approve' && $service->approveSubmission($data + ['id' => $id], Auth::user())) {
            flash('Submission approved successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
