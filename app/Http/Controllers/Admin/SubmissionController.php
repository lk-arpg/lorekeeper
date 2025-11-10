<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Prompt\PromptCategory;
use App\Models\Raffle\Raffle;
use App\Models\Submission\Submission;
use App\Services\SubmissionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller {
    /**
     * Shows the submission index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubmissionIndex(Request $request, $status = null) {
        $submissions = Submission::with('prompt')->where('status', $status ? ucfirst($status) : 'Pending')->whereNotNull('prompt_id');
        $data = $request->only(['prompt_category_id', 'sort']);
        if (isset($data['prompt_category_id']) && $data['prompt_category_id'] != 'none') {
            $submissions->whereHas('prompt', function ($query) use ($data) {
                $query->where('prompt_category_id', $data['prompt_category_id']);
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

        return view('admin.submissions.index', [
            'submissions' => $submissions->paginate(30)->appends($request->query()),
            'categories'  => ['none' => 'Any Category'] + PromptCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'isClaims'    => false,
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
        $submission = Submission::whereNotNull('prompt_id')->where('id', $id)->where('status', '!=', 'Draft')->first();
        $inventory = isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null;
        if (!$submission) {
            abort(404);
        }

        return view('admin.submissions.submission', [
            'submission'       => $submission,
            'inventory'        => $inventory,
            'rewardsData'      => isset($submission->data['rewards']) ? parseAssetData($submission->data['rewards']) : null,
            'itemsrow'         => Item::all()->keyBy('id'),
            'page'             => 'submission',
            'expanded_rewards' => config('lorekeeper.extensions.character_reward_expansion.expanded'),
            'characters'       => Character::visible(Auth::user() ?? null)->myo(0)->orderBy('slug', 'DESC')->get()->pluck('fullName', 'slug')->toArray(),
        ] + ($submission->status == 'Pending' ? [
            'characterCurrencies' => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'items'               => Item::orderBy('name')->pluck('name', 'id'),
            'currencies'          => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'              => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'             => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'count'               => Submission::where('prompt_id', $submission->prompt_id)->where('status', 'Approved')->where('user_id', $submission->user_id)->count(),
        ] : []));
    }

    /**
     * Shows the claim index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getClaimIndex(Request $request, $status = null) {
        $submissions = Submission::where('status', $status ? ucfirst($status) : 'Pending')->whereNull('prompt_id');
        $data = $request->only(['sort']);
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

        return view('admin.submissions.index', [
            'submissions' => $submissions->paginate(30),
            'isClaims'    => true,
        ]);
    }

    /**
     * Shows the claim detail page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getClaim($id) {
        $submission = Submission::whereNull('prompt_id')->where('id', $id)->where('status', '!=', 'Draft')->first();
        $inventory = isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null;
        if (!$submission) {
            abort(404);
        }

        return view('admin.submissions.submission', [
            'submission'       => $submission,
            'inventory'        => $inventory,
            'itemsrow'         => Item::all()->keyBy('id'),
            'expanded_rewards' => config('lorekeeper.extensions.character_reward_expansion.expanded'),
            'characters'       => Character::visible(Auth::user() ?? null)->myo(0)->orderBy('slug', 'DESC')->get()->pluck('fullName', 'slug')->toArray(),
        ] + ($submission->status == 'Pending' ? [
            'characterCurrencies' => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'items'               => Item::orderBy('name')->pluck('name', 'id'),
            'currencies'          => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'              => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'             => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'count'               => Submission::where('prompt_id', $id)->where('status', 'Approved')->where('user_id', $submission->user_id)->count(),
            'rewardsData'         => isset($submission->data['rewards']) ? parseAssetData($submission->data['rewards']) : null,
        ] : []));
    }

    /**
     * Creates a new submission.
     *
     * @param App\Services\SubmissionManager $service
     * @param int                            $id
     * @param string                         $action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSubmission(Request $request, SubmissionManager $service, $id, $action) {
        $data = $request->only(['slug',  'character_rewardable_quantity', 'character_rewardable_id',  'character_rewardable_type', 'character_currency_id', 'rewardable_type', 'rewardable_id', 'quantity', 'staff_comments']);
        if ($action == 'reject' && $service->rejectSubmission($request->only(['staff_comments']) + ['id' => $id], Auth::user())) {
            flash('Submission rejected successfully.')->success();
        } elseif ($action == 'cancel' && $service->cancelSubmission($request->only(['staff_comments']) + ['id' => $id], Auth::user())) {
            flash('Submission canceled successfully.')->success();

            return redirect()->to('admin/submissions');
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
