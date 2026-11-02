<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Models\Gallery\Gallery;
use App\Models\Gallery\GallerySubmission;
use App\Services\GalleryManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller {
    /**
     * Shows the submission index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubmissionIndex(Request $request, $status = null) {
        $submissions = GallerySubmission::collaboratorApproved()->where('status', $status ? ucfirst($status) : 'Pending');
        $data = $request->only(['gallery_id', 'sort']);
        if (isset($data['gallery_id'])) {
            $submissions->where(function ($query) use ($data) {
                $query->where('gallery_id', $data['gallery_id']);
            });
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $submissions->sortNewest();
                    break;
                case 'oldest':
                    $submissions->sortNewest(true);
                    break;
            }
        } else {
            $submissions->sortNewest(true);
        }
        if ($status == 'pending' || !$status) {
            $submissions = $submissions->orderBy('created_at', 'ASC');
        } else {
            $submissions = $submissions->orderBy('created_at', 'DESC');
        }

        return view('admin.galleries.submissions_index', [
            'submissions' => $submissions->paginate(10)->appends($request->query()),
            'galleries'   => ['' => 'Any Gallery'] + Gallery::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the index of submissions in the context of currency rewards.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCurrencyIndex(Request $request, $status = null) {
        $submissions = GallerySubmission::requiresAward()->where('is_valued', !$status || $status == 'pending' ? 0 : 1);
        $data = $request->only(['gallery_id', 'sort']);
        if (isset($data['gallery_id'])) {
            $submissions->where(function ($query) use ($data) {
                $query->where('gallery_id', $data['gallery_id']);
            });
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $submissions->sortNewest();
                    break;
                case 'oldest':
                    $submissions->sortNewest(true);
                    break;
            }
        } else {
            $submissions->sortNewest(true);
        }
        if ($status == 'pending' || !$status) {
            $submissions = $submissions->orderBy('created_at', 'ASC');
        } else {
            $submissions = $submissions->orderBy('created_at', 'DESC');
        }

        return view('admin.galleries.submissions_currency_index', [
            'submissions' => $submissions->paginate(10)->appends($request->query()),
            'galleries'   => ['' => 'Any Gallery'] + Gallery::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'currency'    => Currency::find(Settings::get('group_currency')),
        ]);
    }

    /**
     * Edits gallery submissions.
     *
     * @param App\Services\GalleryManager $service
     * @param int                         $id
     * @param string                      $action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditSubmission(Request $request, GalleryManager $service, $id, $action) {
        if (!$id) {
            flash('Invalid submission selected.')->error();
        }

        if ($id && $action) {
            switch ($action) {
                default:
                    flash('Invalid action selected.')->error();
                    break;
                case 'accept':
                    return $this->postVote($id, $service, $action);
                    break;
                case 'reject':
                    return $this->postVote($id, $service, $action);
                    break;
                case 'comment':
                    return $this->postStaffComments($id, $request->only(['staff_comments', 'alert_user']), $service);
                    break;
                case 'value':
                    return $this->postValue($id, $request->only(['value', 'ineligible']), $service);
                    break;
            }
        }

        return redirect()->back();
    }

    /**
     * Casts a vote for a submission's approval or denial.
     *
     * @param int                         $id
     * @param string                      $action
     * @param App\Services\GalleryManager $service
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function postVote($id, GalleryManager $service, $action) {
        $submission = GallerySubmission::where('id', $id)->where('status', 'Pending')->first();
        if (!$submission) {
            throw new \Exception('Invalid submission.');
        }

        if ($action == 'reject' && $service->castVote($action, $submission, Auth::user())) {
            flash('Voted to reject successfully.')->success();
        } elseif ($action == 'accept' && $service->castVote($action, $submission, Auth::user())) {
            flash('Voted to approve successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Posts staff comments for a gallery submission.
     *
     * @param int                         $id
     * @param string                      $data
     * @param App\Services\GalleryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postStaffComments($id, $data, GalleryManager $service) {
        if ($service->postStaffComments($id, $data, Auth::user())) {
            flash('Comments updated succesfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Posts group currency evaluation for a gallery submission.
     *
     * @param int                         $id
     * @param string                      $data
     * @param App\Services\GalleryManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postValue($id, $data, GalleryManager $service) {
        if ($service->postValueSubmission($id, $data, Auth::user())) {
            flash('Submission evaluated succesfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
