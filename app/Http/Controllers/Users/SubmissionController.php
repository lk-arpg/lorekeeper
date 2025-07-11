<?php

namespace App\Http\Controllers\Users;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Gallery\GalleryCollaborator;
use App\Models\Gallery\GallerySubmission;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Prompt\Prompt;
use App\Models\Raffle\Raffle;
use App\Models\Submission\Submission;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\SubmissionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Submission Controller
    |--------------------------------------------------------------------------
    |
    | Handles prompt submissions and claims for the user.
    |
    */

    /**********************************************************************************************

        PROMPT SUBMISSIONS

    **********************************************************************************************/

    /**
     * Shows the user's submission log.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request) {
        $submissions = Submission::with('prompt')->where('user_id', Auth::user()->id)->whereNotNull('prompt_id');
        $type = $request->get('type');
        if (!$type) {
            $type = 'Pending';
        }

        $submissions = $submissions->where('status', ucfirst($type));

        return view('home.submissions', [
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
    public function getSubmission($id) {
        $submission = Submission::viewable(Auth::user())->where('id', $id)->whereNotNull('prompt_id')->first();
        $inventory = isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null;
        if (!$submission) {
            abort(404);
        }

        return view('home.submission', [
            'submission' => $submission,
            'user'       => $submission->user,
            'categories' => ItemCategory::orderBy('sort', 'DESC')->get(),
            'inventory'  => $inventory,
            'itemsrow'   => Item::all()->keyBy('id'),
            'isClaim'    => false,
        ]);
    }

    /**
     * Shows the submit page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewSubmission(Request $request) {
        $closed = !Settings::get('is_prompts_open');
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->get();

        if (config('lorekeeper.settings.allow_gallery_submissions_on_prompts')) {
            $collaboratorIds = GalleryCollaborator::where('user_id', Auth::user()->id)->where('has_approved', 1)->pluck('gallery_submission_id')->toArray();

            $gallerySubmissions = GallerySubmission::where('is_visible', true)->where('user_id', Auth::user()->id)->orWhereIn('id', $collaboratorIds)->orderBy('id', 'DESC')->get()->pluck('title', 'id');

            $gallerySubmissions = $gallerySubmissions->map(function ($item, $key) {
                return '"'.$item.'" by '.Auth::user()->name;
            });
        } else {
            $gallerySubmissions = [];
        }

        return view('home.create_submission', [
            'closed'  => $closed,
            'isClaim' => false,
        ] + ($closed ? [] : [
            'submission'             => new Submission,
            'prompts'                => Prompt::active()->sortAlphabetical()->pluck('name', 'id')->toArray(),
            'characterCurrencies'    => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'categories'             => ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'item_filter'            => Item::orderBy('name')->released()->get()->keyBy('id'),
            'items'                  => Item::orderBy('name')->released()->pluck('name', 'id'),
            'character_items'        => Item::whereIn('item_category_id', ItemCategory::where('is_character_owned', 1)->pluck('id')->toArray())->orderBy('name')->released()->pluck('name', 'id'),
            'currencies'             => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'inventory'              => $inventory,
            'page'                   => 'submission',
            'expanded_rewards'       => config('lorekeeper.extensions.character_reward_expansion.expanded'),
            'userGallerySubmissions' => $gallerySubmissions,
        ]));
    }

    /**
     * Shows the edit submission page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSubmission(Request $request, $id) {
        $closed = !Settings::get('is_prompts_open');
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->get();
        $submission = Submission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (!$submission) {
            abort(404);
        }

        if (config('lorekeeper.settings.allow_gallery_submissions_on_prompts')) {
            $gallerySubmissions = GallerySubmission::where('user_id', Auth::user()->id)->where('status', 'Accepted')->orderBy('id', 'DESC')->get()->pluck('title', 'id');
            $gallerySubmissions = $gallerySubmissions->map(function ($item, $key) {
                return '"'.$item.'" by '.Auth::user()->name;
            });
        } else {
            $gallerySubmissions = [];
        }

        return view('home.edit_submission', [
            'closed'              => $closed,
            'isClaim'             => false,
        ] + ($closed ? [] : [
            'submission'             => $submission,
            'prompts'                => Prompt::active()->sortAlphabetical()->pluck('name', 'id')->toArray(),
            'characterCurrencies'    => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'categories'             => ItemCategory::orderBy('sort', 'DESC')->get(),
            'item_filter'            => Item::orderBy('name')->released()->get()->keyBy('id'),
            'items'                  => Item::orderBy('name')->released()->pluck('name', 'id'),
            'character_items'        => Item::whereIn('item_category_id', ItemCategory::where('is_character_owned', 1)->pluck('id')->toArray())->orderBy('name')->released()->pluck('name', 'id'),
            'currencies'             => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'inventory'              => $inventory,
            'page'                   => 'submission',
            'expanded_rewards'       => config('lorekeeper.extensions.character_reward_expansion.expanded'),
            'selectedInventory'      => isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null,
            'count'                  => Submission::where('prompt_id', $submission->prompt_id)->where('status', 'Approved')->where('user_id', $submission->user_id)->count(),
            'userGallerySubmissions' => $gallerySubmissions,
        ]));
    }

    /**
     * Shows character information.
     *
     * @param string $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterInfo($slug) {
        $character = Character::visible()->where('slug', $slug)->first();

        return view('home._character', [
            'character' => $character,
        ]);
    }

    /**
     * Shows prompt information.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPromptInfo($id) {
        $prompt = Prompt::active()->where('id', $id)->first();
        if (!$prompt) {
            return response(404);
        }

        return view('home._prompt', [
            'prompt' => $prompt,
            'count'  => Submission::where('prompt_id', $id)->where('status', 'Approved')->where('user_id', Auth::user()->id)->count(),
        ]);
    }

    /**
     * Shows prompt requirement information.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPromptRequirementInfo($id) {
        $prompt = Prompt::active()->where('id', $id)->first();
        if (!$prompt) {
            return response(404);
        }

        return view('home._prompt_requirements', [
            'prompt' => $prompt,
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
    public function postNewSubmission(Request $request, SubmissionManager $service, $draft = false) {
        $request->validate(Submission::$createRules);
        if ($submission = $service->createSubmission(
            $request->only([
                'url', 'prompt_id', 'comments', 'slug', 'character_rewardable_type', 'character_rewardable_id', 'character_rewardable_quantity',
                'rewardable_type', 'rewardable_id', 'quantity', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity',
                'gallery_submission_id',
            ]),
            Auth::user(),
            false,
            $draft
        )) {
            if ($submission->status == 'Draft') {
                flash('Draft created successfully.')->success();

                return redirect()->to('submissions/draft/'.$submission->id);
            } else {
                flash('Prompt submitted successfully.')->success();

                return redirect()->to('submissions/view/'.$submission->id);
            }
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back()->withInput();
        }

        return redirect()->to('submissions');
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
    public function postEditSubmission(Request $request, SubmissionManager $service, $id, $submit = false) {
        $submission = Submission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (!$submission) {
            abort(404);
        }

        $request->validate(Submission::$updateRules);
        if ($submit && $service->editSubmission($submission, $request->only([
            'url', 'prompt_id', 'comments', 'slug', 'character_rewardable_type', 'character_rewardable_id', 'character_rewardable_quantity',
            'rewardable_type', 'rewardable_id', 'quantity', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity',
            'gallery_submission_id',
        ]), Auth::user(), false, $submit)) {
            flash('Draft submitted successfully.')->success();
        } elseif ($service->editSubmission($submission, $request->only([
            'url', 'prompt_id', 'comments', 'slug', 'character_rewardable_type', 'character_rewardable_id', 'character_rewardable_quantity',
            'rewardable_type', 'rewardable_id', 'quantity', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity',
            'gallery_submission_id',
        ]), Auth::user())) {
            flash('Draft saved successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back()->withInput();
        }

        return redirect()->to('submissions/view/'.$submission->id);
    }

    /**
     * Deletes a submission draft.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSubmission(Request $request, SubmissionManager $service, $id) {
        $submission = Submission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (!$submission) {
            abort(404);
        }

        if ($service->deleteSubmission($submission, Auth::user())) {
            flash('Draft deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }

        return redirect()->to('submissions?type=draft');
    }

    /**
     * Cancels a submission and makes it into a draft again.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCancelSubmission(Request $request, SubmissionManager $service, $id) {
        $submission = Submission::where('id', $id)->where('status', 'Pending')->where('user_id', Auth::user()->id)->first();
        if (!$submission) {
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

        return redirect()->to('submissions/draft/'.$submission->id);
    }

    /**********************************************************************************************

        CLAIMS

    **********************************************************************************************/

    /**
     * Shows the user's claim log.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getClaimsIndex(Request $request) {
        $submissions = Submission::where('user_id', Auth::user()->id)->whereNull('prompt_id');
        $type = $request->get('type');
        if (!$type) {
            $type = 'Pending';
        }

        $submissions = $submissions->where('status', ucfirst($type));

        return view('home.submissions', [
            'submissions' => $submissions->orderBy('id', 'DESC')->paginate(20)->appends($request->query()),
            'isClaims'    => true,
        ]);
    }

    /**
     * Shows the claim page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getClaim($id) {
        $submission = Submission::viewable(Auth::user())->where('id', $id)->whereNull('prompt_id')->first();
        $inventory = isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null;
        if (!$submission) {
            abort(404);
        }

        return view('home.submission', [
            'submission' => $submission,
            'user'       => $submission->user,
            'categories' => ItemCategory::orderBy('sort', 'DESC')->get(),
            'itemsrow'   => Item::all()->keyBy('id'),
            'inventory'  => $inventory,
            'isClaim'    => true,
        ]);
    }

    /**
     * Shows the submit claim page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNewClaim(Request $request) {
        $closed = !Settings::get('is_claims_open');
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->get();

        return view('home.create_submission', [
            'closed'  => $closed,
            'isClaim' => true,
        ] + ($closed ? [] : [
            'submission'             => new Submission,
            'characterCurrencies'    => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'categories'             => ItemCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get(),
            'inventory'              => $inventory,
            'item_filter'            => Item::orderBy('name')->released()->get()->keyBy('id'),
            'items'                  => Item::orderBy('name')->released()->pluck('name', 'id'),
            'currencies'             => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'raffles'                => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'page'                   => 'submission',
            'expanded_rewards'       => config('lorekeeper.extensions.character_reward_expansion.expanded'),
            'userGallerySubmissions' => [],
        ]));
    }

    /**
     * Shows the edit submission page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditClaim(Request $request, $id) {
        $closed = !Settings::get('is_claims_open');
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->get();
        $submission = Submission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (!$submission) {
            abort(404);
        }

        return view('home.edit_submission', [
            'closed'                => $closed,
            'isClaim'               => true,
        ] + ($closed ? [] : [
            'submission'             => $submission,
            'characterCurrencies'    => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'character_items'        => Item::whereIn('item_category_id', ItemCategory::where('is_character_owned', 1)->pluck('id')->toArray())->orderBy('name')->released()->pluck('name', 'id'),
            'categories'             => ItemCategory::orderBy('sort', 'DESC')->get(),
            'currencies'             => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'item_filter'            => Item::orderBy('name')->released()->get()->keyBy('id'),
            'items'                  => Item::orderBy('name')->released()->pluck('name', 'id'),
            'inventory'              => $inventory,
            'raffles'                => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'page'                   => 'submission',
            'expanded_rewards'       => config('lorekeeper.extensions.character_reward_expansion.expanded'),
            'selectedInventory'      => isset($submission->data['user']) ? parseAssetData($submission->data['user']) : null,
            'userGallerySubmissions' => [],
        ]));
    }

    /**
     * Creates a new claim.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $draft
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postNewClaim(Request $request, SubmissionManager $service, $draft = false) {
        $request->validate(Submission::$createRules);
        if ($submission = $service->createSubmission($request->only(['url', 'comments', 'stack_id', 'stack_quantity', 'slug', 'character_rewardable_type', 'character_rewardable_id', 'character_rewardable_quantity', 'rewardable_type', 'rewardable_id', 'quantity', 'currency_id', 'currency_quantity']), Auth::user(), true, $draft)) {
            if ($submission->status == 'Draft') {
                flash('Draft created successfully.')->success();

                return redirect()->to('claims/draft/'.$submission->id);
            } else {
                flash('Claim submitted successfully.')->success();

                return redirect()->to('claims/view/'.$submission->id);
            }
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back()->withInput();
        }

        return redirect()->to('claims');
    }

    /**
     * Edits a claim draft.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $id
     * @param mixed                          $submit
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditClaim(Request $request, SubmissionManager $service, $id, $submit = false) {
        $submission = Submission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (!$submission) {
            abort(404);
        }

        $request->validate(Submission::$createRules);
        if ($submit && $service->editSubmission($submission, $request->only(['url', 'comments', 'stack_id', 'stack_quantity', 'slug', 'character_rewardable_type', 'character_rewardable_id', 'character_rewardable_quantity', 'rewardable_type', 'rewardable_id', 'quantity', 'currency_id', 'currency_quantity']), Auth::user(), true, $submit)) {
            flash('Draft submitted successfully.')->success();

            return redirect()->to('claims/draft/'.$submission->id);
        } elseif ($service->editSubmission($submission, $request->only(['url', 'comments', 'slug', 'character_rewardable_type', 'character_rewardable_id', 'character_rewardable_quantity', 'rewardable_type', 'rewardable_id', 'quantity', 'stack_id', 'stack_quantity', 'currency_id', 'currency_quantity']), Auth::user(), true)) {
            flash('Draft saved successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back()->withInput();
        }

        return redirect()->to('claims?type=draft');
    }

    /**
     * Deletes a claim draft.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteClaim(Request $request, SubmissionManager $service, $id) {
        $submission = Submission::where('id', $id)->where('status', 'Draft')->where('user_id', Auth::user()->id)->first();
        if (!$submission) {
            abort(404);
        }

        if ($service->deleteSubmission($submission, Auth::user())) {
            flash('Draft deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }

        return redirect()->to('claims?type=draft');
    }

    /**
     * Cancels a claim and makes it into a draft again.
     *
     * @param App\Services\SubmissionManager $service
     * @param mixed                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCancelClaim(Request $request, SubmissionManager $service, $id) {
        $submission = Submission::where('id', $id)->where('status', 'Pending')->where('user_id', Auth::user()->id)->first();
        if (!$submission) {
            abort(404);
        }

        if ($service->cancelSubmission($submission, Auth::user())) {
            flash('Claim returned to drafts successfully. You may wish to delete the draft completely, you may do that from the Edit Draft page below.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }

        return redirect()->to('claims/draft/'.$submission->id);
    }
}
