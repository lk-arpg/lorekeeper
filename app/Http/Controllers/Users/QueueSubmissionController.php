<?php
namespace App\Http\Controllers\Users;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Queue\Queue;
use App\Models\Queue\QueueSubmission;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\QueueSubmissionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueSubmissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Submission Controller
    |--------------------------------------------------------------------------
    |
    | Handles queue submissions and claims for the user.
    |
    */

    /**********************************************************************************************

        QUEUE SUBMISSIONS

    **********************************************************************************************/

    /**
     * Shows the user's submission log.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $submissions = QueueSubmission::with('queue')->where('user_id', Auth::user()->id)->whereNotNull('queue_id');
        $type        = $request->get('type');
        if (! $type) {
            $type = 'Pending';
        }

        $submissions = $submissions->where('status', ucfirst($type));

        return view('home.queues.submissions', [
            'submissions' => $submissions->orderBy('id', 'DESC')->paginate(20)->appends($request->query()),
            'isClaims'    => false,
        ]);
    }

    /**
     * Shows the submission page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubmission($id)
    {
        $submission = QueueSubmission::viewable(Auth::user())->where('id', $id)->whereNotNull('queue_id')->first();

        $inventory  = isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null;
        if (! $submission) {
            abort(404);
        }

        $queue = $submission->queue;

        return view('home.queues.submission', [
            'submission' => $submission,
            'user'       => $submission->user,
            'categories' => ItemCategory::orderBy('sort', 'DESC')->get(),
            'inventory'  => $inventory,
            'itemsrow'   => Item::all()->keyBy('id'), //this keeps track of consumed items and will change if the prompt's items change so let's not change it
            'queue'      => $queue,
        ] + $queue->service->getActData($queue));
    }

    /**
     * Shows the submit page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewSubmission(Request $request, $id)
    {
        $closed = ! Settings::get('is_queue_open');

        $queue = Queue::active()->find($id);
        if (! $queue) {
            abort(404);
        }

        return view('home.queues.create_submission', [
            'closed' => $closed,
            'queue'  => $queue,
        ] + ($closed ? [] : [
            'submission'  => new QueueSubmission,
            'page'        => 'submission',
            'count'       => QueueSubmission::where('queue_id', $queue->id)->where('status', 'Approved')->where('user_id', Auth::user()->id)->count(),
        ] + $queue->service->getActData($queue) + ($queue->configSet('item_consume') ? [
            'inventory'  => isset($queue->data['items']) ? UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->whereIn('item_id', $queue->data['items'])->get() : UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->get(),
            'itemsrow'   => Item::all()->keyBy('id'),//this keeps track of consumed items and will change if the prompt's items change so let's not change it
            'categories' => ItemCategory::orderBy('sort', 'DESC')->get(),
            'item_filter' => isset($queue->data['items']) ? Item::whereIn('id', $queue->data['items'])->get()->keyBy('id') : Item::orderBy('name')->released()->get()->keyBy('id'),
        ] : [])));
    }

    /**
     * Shows the edit submission page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSubmission(Request $request, $id)
    {

        $closed     = ! Settings::get('is_queue_open');
        $submission = QueueSubmission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (! $submission) {
            abort(404);
        }

        $queue = $submission->queue;
        if (! $queue) {
            abort(404);
        }

        return view('home.queues.edit_submission', [
            'closed'  => $closed,
            'queue'  => $queue,
        ] + ($closed ? [] : [
            'submission'          => $submission,
            'count'               => QueueSubmission::where('queue_id', $submission->queue_id)->where('status', 'Approved')->where('user_id', $submission->user_id)->count(),
        ]+ $queue->service->getActData($queue) + ($queue->configSet('item_consume') ? [
            'inventory'  => isset($queue->data['items']) ? UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->whereIn('item_id', $queue->data['items'])->get() : UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->get(),
            'itemsrow'   => Item::all()->keyBy('id'), //this keeps track of consumed items and will change if the prompt's items change so let's not change it
            'categories' => ItemCategory::orderBy('sort', 'DESC')->get(),
            'item_filter' => isset($queue->data['items']) ? Item::whereIn('id', $queue->data['items'])->get()->keyBy('id') : Item::orderBy('name')->released()->get()->keyBy('id'),
            'page'                => 'queue-submission',
            'selectedInventory'   => isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null,
        ] : [])));
    }

    /**
     * Shows character information.
     *
     * @param string $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterInfo($slug)
    {
        $character = Character::visible()->where('slug', $slug)->first();

        return view('home.queues._character', [
            'character' => $character,
        ]);
    }

    /**
     * Creates a new submission.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $draft
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewSubmission(Request $request, QueueSubmissionManager $service, $id, $draft = false)
    {
        $queue = Queue::active()->where('id', $id)->first();
        if (! $queue) {
            throw new \Exception('Invalid queue selected.');
        }

        $request->validate(QueueSubmission::$createRules);
        if ($submission = $service->createSubmission($queue,
            $request->all(),
            Auth::user(),
            $draft
        )) {
            if ($submission->status == 'Draft') {
                flash('Draft created successfully.')->success();

                return redirect()->to('queue-submissions/draft/' . $submission->id);
            } else {
                flash('Queue submitted successfully.')->success();

                return redirect()->to('queue-submissions/view/' . $submission->id);
            }
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back()->withInput();
        }

        return redirect()->to('queue-submissions');
    }

    /**
     * Edits a submission draft.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $id
     * @param mixed                          $submit
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditSubmission(Request $request, QueueSubmissionManager $service, $id, $submit = false)
    {

        $submission = QueueSubmission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (! $submission) {
            abort(404);
        }

        $request->validate(QueueSubmission::$updateRules);
        if ($submit && $service->editSubmission($submission, $request->all(), Auth::user(), $submit)) {
            flash('Draft submitted successfully.')->success();
        } elseif ($service->editSubmission($submission, $request->all(), Auth::user())) {
            flash('Draft saved successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back()->withInput();
        }

        return redirect()->to('queue-submissions/view/' . $submission->id);
    }

    /**
     * Deletes a submission draft.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSubmission(Request $request, QueueSubmissionManager $service, $id)
    {
        $submission = QueueSubmission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (! $submission) {
            abort(404);
        }

        if ($service->deleteSubmission($submission, $request->all() + ['submission_id' => $submission->id], Auth::user())) {
            flash('Draft deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }

        return redirect()->to('queue-submissions?type=draft');
    }

    /**
     * Cancels a submission and makes it into a draft again.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCancelSubmission(Request $request, QueueSubmissionManager $service, $id)
    {
        $submission = QueueSubmission::where('id', $id)->where('status', 'Pending')->where('user_id', Auth::user()->id)->first();
        if (! $submission) {
            abort(404);
        }

        if ($service->cancelSubmission($submission, Auth::user())) {
            flash('Submission returned to drafts successfully. If you wish to delete the draft entirely you may do so from the Edit Draft page.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }

        return redirect()->to('queue-submissions/draft/' . $submission->id);
    }

}
