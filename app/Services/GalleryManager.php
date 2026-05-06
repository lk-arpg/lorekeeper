<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Models\Gallery\Gallery;
use App\Models\Gallery\GalleryCharacter;
use App\Models\Gallery\GalleryCollaborator;
use App\Models\Gallery\GalleryFavorite;
use App\Models\Gallery\GallerySubmission;
use App\Models\Prompt\Prompt;
use App\Models\User\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class GalleryManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Gallery Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of gallery submissions.
    |
    */

    /**
     * Creates a new gallery submission.
     *
     * @param array $data
     * @param array $currencyFormData
     * @param User  $user
     *
     * @return bool|GallerySubmission
     */
    public function createSubmission($data, $currencyFormData, $user) {
        DB::beginTransaction();

        try {
            //  Check that submissions are open
            if (!Settings::get('gallery_submissions_open')) {
                throw new \Exception('Gallery submissions are currently closed.');
            }
            // Check that the gallery exists and can be submitted to
            $gallery = Gallery::find($data['gallery_id']);
            if (!$gallery) {
                throw new \Exception('Invalid gallery selected.');
            }
            if (!$gallery->submissions_open && !$user->hasPower('manage_submissions')) {
                throw new \Exception('You cannot submit to this gallery.');
            }
            if ((isset($gallery->start_at) && $gallery->start_at->isFuture()) || (isset($gallery->end_at) && $gallery->end_at->isPast())) {
                throw new \Exception('This gallery\'s submissions aren\'t open.');
            }

            // Check that associated collaborators exist
            if (isset($data['collaborator_id'])) {
                $collaborators = User::whereIn('id', $data['collaborator_id'])->get();
                if (count($collaborators) != count($data['collaborator_id'])) {
                    throw new \Exception('One or more of the selected collaborators does not exist, or you have entered a duplicate.');
                }
            } else {
                $collaborators = [];
            }

            // Check that associated participants exist
            if (isset($data['participant_id'])) {
                $participants = User::whereIn('id', $data['participant_id'])->get();
                if (count($participants) != count($data['participant_id'])) {
                    throw new \Exception('One or more of the selected participants does not exist, or you have entered a duplicate.');
                }
            } else {
                $participants = [];
            }

            // Check that associated characters exist
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->visible()->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters does not exist, or you have entered a duplicate.');
                }
            } else {
                $characters = [];
            }

            // Check that the selected prompt exists and can be submitted to
            if (isset($data['prompt_id'])) {
                $prompt = Prompt::active()->find($data['prompt_id']);
                if (!$prompt) {
                    throw new \Exception('Invalid prompt selected.');
                }
            }

            $submission = GallerySubmission::create([
                'user_id'    => $user->id,
                'gallery_id' => $gallery->id,
                'status'     => 'Pending',
                'title'      => $data['title'],
                'is_visible' => 1,
            ]);

            $data = $this->populateData($data);

            if (isset($currencyFormData) && $currencyFormData) {
                $data['data']['currencyData'] = $currencyFormData;
                $data['data']['total'] = calculateGroupCurrency($currencyFormData);
                $data['data'] = collect($data['data'])->toJson();
            }

            $submission->update($data);

            if (isset($data['image']) && $data['image']) {
                $this->processImage($data, $submission);
            }
            $submission->update();

            if (isset($data['collaborator_id']) && $collaborators->count()) {
                // Attach any collaborators to the submission
                foreach ($data['collaborator_id'] as $key=> $collaborator) {
                    GalleryCollaborator::create([
                        'user_id'               => $collaborator,
                        'gallery_submission_id' => $submission->id,
                        'data'                  => $data['collaborator_data'][$key],
                        'has_approved'          => $collaborator == $user->id ? 1 : 0,
                    ]);

                    // Notify collaborators (but not the submitting user)
                    if ($collaborator != $user->id) {
                        Notifications::create('GALLERY_SUBMISSION_COLLABORATOR', User::find($collaborator), [
                            'sender_url'    => $user->url,
                            'sender'        => $user->name,
                            'submission_id' => $submission->id,
                        ]);
                    }
                }
            }

            if (isset($data['participant_id']) && $participants->count()) {
                // Attach any participants to the submission
                foreach ($data['participant_id'] as $key=> $participant) {
                    GalleryCollaborator::create([
                        'user_id'               => $participant,
                        'gallery_submission_id' => $submission->id,
                        'data'                  => null,
                        'has_approved'          => 1,
                        'type'                  => $data['participant_type'][$key],
                    ]);
                }
            }

            // Attach any characters to the submission
            foreach ($characters as $character) {
                GalleryCharacter::create([
                    'character_id'          => $character->id,
                    'gallery_submission_id' => $submission->id,
                ]);
            }

            if (!$submission->collaborators->count() && (!Settings::get('gallery_submissions_require_approval') || (Settings::get('gallery_submissions_require_approval') && $submission->gallery->votes_required == 0))) {
                $this->acceptSubmission($submission);
            }

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a gallery submission.
     *
     * @param GallerySubmission $submission
     * @param array             $data
     * @param User              $user
     *
     * @return bool|GallerySubmission
     */
    public function updateSubmission($submission, $data, $user) {
        DB::beginTransaction();

        try {
            // Check that the user can edit the submission
            if (!$submission->user_id == $user->id && !$user->hasPower('manage_submissions')) {
                throw new \Exception("You can't edit this submission.");
            }

            if ($submission->status == 'Rejected') {
                throw new \Exception('This submission has been rejected and cannot be edited.');
            }

            // Check that there is text and/or an image, including if there is an existing image (via the existence of a hash)
            if ((!isset($data['image']) && !isset($submission->hash)) && !$data['text']) {
                throw new \Exception('Please submit either text or an image.');
            }

            if ($user->isStaff) {
                if (!$this->logAdminAction($user, 'Edited Gallery Submission', 'Edited gallery submission '.$submission->displayName)) {
                    throw new \Exception('Failed to log admin action.');
                }
            }

            // If still pending, perform validation on and process collaborators and participants
            if ($submission->status == 'Pending') {
                // Check that associated collaborators exist
                if (isset($data['collaborator_id'])) {
                    $collaborators = User::whereIn('id', $data['collaborator_id'])->get();
                    if (count($collaborators) != count($data['collaborator_id'])) {
                        throw new \Exception('One or more of the selected users does not exist, or you have entered a duplicate.');
                    }
                } else {
                    $collaborators = [];
                }

                // Fetch collaborator approval data
                $collaboratorApproval = $submission->collaborators->pluck('has_approved', 'user_id');
                // Remove all collaborators from the submission so they can be reattached with new data
                $submission->collaborators()->delete();

                if (isset($data['collaborator_id']) && $collaborators->count()) {
                    // Attach any collaborators to the submission
                    foreach ($data['collaborator_id'] as $key=> $collaborator) {
                        GalleryCollaborator::create([
                            'user_id'               => $collaborator,
                            'gallery_submission_id' => $submission->id,
                            'data'                  => $data['collaborator_data'][$key],
                            'has_approved'          => $collaboratorApproval[$collaborator] ?? ($collaborator == $user->id ? 1 : 0),
                        ]);
                    }
                }

                // Check that associated participants exist
                if (isset($data['participant_id'])) {
                    $participants = User::whereIn('id', $data['participant_id'])->get();
                    if (count($participants) != count($data['participant_id'])) {
                        throw new \Exception('One or more of the selected participants does not exist, or you have entered a duplicate.');
                    }
                } else {
                    $participants = [];
                }

                // Remove all participants from the submission so they can be reattached with new data
                $submission->participants()->delete();

                if (isset($data['participant_id']) && $participants->count()) {
                    // Attach any participants to the submission
                    foreach ($data['participant_id'] as $key=> $participant) {
                        GalleryCollaborator::create([
                            'user_id'               => $participant,
                            'gallery_submission_id' => $submission->id,
                            'data'                  => null,
                            'has_approved'          => 1,
                            'type'                  => $data['participant_type'][$key],
                        ]);
                    }
                }
            }

            // Check that associated characters exist
            if (isset($data['slug'])) {
                $characters = Character::myo(0)->visible()->whereIn('slug', $data['slug'])->get();
                if (count($characters) != count($data['slug'])) {
                    throw new \Exception('One or more of the selected characters does not exist, or you have entered a duplicate.');
                }
            } else {
                $characters = [];
            }

            // Remove all characters from the submission so they can be reattached with new data
            $submission->characters()->delete();

            // Attach any characters to the submission
            foreach ($characters as $character) {
                GalleryCharacter::create([
                    'character_id'          => $character->id,
                    'gallery_submission_id' => $submission->id,
                ]);
            }

            // Check that the selected prompt exists and can be submitted to
            if (isset($data['prompt_id'])) {
                $prompt = $user->hasPower('manage_submissions') ? Prompt::find($data['prompt_id']) : Prompt::active()->find($data['prompt_id']);
                if (!$prompt) {
                    throw new \Exception('Invalid prompt selected.');
                }
            }

            if ((isset($submission->parsed_description) && $submission->parsed_description) && !isset($data['description'])) {
                $data['parsed_description'] = null;
            }
            if ((isset($submission->parsed_text) && $submission->parsed_text) && !isset($data['text'])) {
                $data['parsed_text'] = null;
            }

            $data = $this->populateData($data);
            if (isset($data['image']) && $data['image']) {
                $this->processImage($data, $submission);
            }

            // Processing relating to staff edits
            if ($user->hasPower('manage_submissions')) {
                if (!isset($data['gallery_id']) && !$data['gallery_id']) {
                    $data['gallery_id'] = $submission->gallery->id;
                }

                $data['staff_id'] = $user->id;
            }

            // Send notifications for staff edits if necessary
            if ($user->hasPower('manage_submissions') && $user->id != $submission->user->id && (isset($data['alert_user']) && $data['alert_user'])) {
                if ($data['gallery_id'] != $submission->gallery_id) {
                    Notifications::create('GALLERY_SUBMISSION_MOVED', $submission->user, [
                        'submission_title' => $submission->title,
                        'submission_id'    => $submission->id,
                        'staff_url'        => $user->url,
                        'staff_name'       => $user->name,
                    ]);
                } else {
                    Notifications::create('GALLERY_SUBMISSION_EDITED', $submission->user, [
                        'submission_title' => $submission->title,
                        'submission_id'    => $submission->id,
                        'staff_url'        => $user->url,
                        'staff_name'       => $user->name,
                    ]);
                }
            }

            $submission->update($data);

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes collaborator edits/approvals on a submission.
     *
     * @param GallerySubmission $submission
     * @param User              $user
     * @param mixed             $data
     *
     * @return bool|GalleryFavorite
     */
    public function editCollaborator($submission, $data, $user) {
        DB::beginTransaction();

        try {
            // Check that the submission is pending and the user is a collaborator on it
            if (!$submission->status == 'Pending') {
                throw new \Exception("This submission isn't pending.");
            }
            if ($submission->collaborators->where('user_id', $user->id)->first() == null) {
                throw new \Exception("You aren't a collaborator on this submission.");
            }

            // Check if the user has requested to be removed from the submission
            if (isset($data['remove_user']) && $data['remove_user']) {
                $submission->collaborators()->where('user_id', $user->id)->delete();
            }
            // Otherwise update the record of their contribution and mark them as having approved
            else {
                $collaborator = $submission->collaborators->where('user_id', $user->id)->first();
                $collaboratorData = ['data' => $data['collaborator_data'][0], 'has_approved' => 1];
                $collaborator->update($collaboratorData);

                // Check if all collaborators have approved, and if so send a notification to the
                // submitting user (unless they are the last to approve-- which shouldn't happen, but)
                if ($submission->collaboratorApproval) {
                    if (Settings::get('gallery_submissions_require_approval') && $submission->gallery->votes_required > 0) {
                        if ($submission->user->id != $user->id) {
                            Notifications::create('GALLERY_COLLABORATORS_APPROVED', $submission->user, [
                                'submission_title' => $submission->title,
                                'submission_id'    => $submission->id,
                            ]);
                        }
                    } else {
                        $this->acceptSubmission($submission);
                    }
                }
            }

            return $this->commitReturn($submission);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Votes on a gallery submission.
     *
     * @param string            $action
     * @param GallerySubmission $submission
     * @param User              $user
     *
     * @return bool
     */
    public function castVote($action, $submission, $user) {
        DB::beginTransaction();

        try {
            if ($submission->status != 'Pending') {
                throw new \Exception('This request cannot be processed.');
            }
            if (!$submission->collaboratorApproval) {
                throw new \Exception("This submission's collaborators have not all approved yet.");
            }

            switch ($action) {
                default:
                    flash('Invalid action.')->error();
                    break;
                case 'accept':
                    $vote = 2;
                    break;
                case 'reject':
                    $vote = 1;
                    break;
            }

            // Get existing vote data if it exists, remove any existing vote data for the user,
            // add the new vote data, and json encode it
            $voteData = (isset($submission->attributes['vote_data']) ? collect(json_decode($submission->attributes['vote_data'], true)) : collect([]));
            $voteData->get($user->id) ? $voteData->pull($user->id) : null;
            $voteData->put($user->id, $vote);
            $submission->vote_data = $voteData->toJson();

            $submission->save();

            // Process the submission if the required number of votes has been reached
            if ($action == 'reject' && $submission->getVoteData()['reject'] >= $submission->gallery->votes_required) {
                $this->rejectSubmission($submission, $user);
            }
            if ($action == 'accept' && $submission->getVoteData()['approve'] >= $submission->gallery->votes_required) {
                $this->acceptSubmission($submission);
            }

            if (!$this->logAdminAction($user, 'Voted on Gallery Submission', 'Voted on gallery submission '.$submission->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes staff comments for a submission.
     *
     * @param User  $user
     * @param mixed $id
     * @param mixed $data
     *
     * @return bool|GalleryFavorite
     */
    public function postStaffComments($id, $data, $user) {
        DB::beginTransaction();

        try {
            $submission = GallerySubmission::find($id);
            // Check that the submission exists and that the user can edit staff comments
            if (!$submission) {
                throw new \Exception('Invalid submission selected.');
            }
            if (!$user->hasPower('manage_submissions')) {
                throw new \Exception("You can't edit staff comments on this submission.");
            }

            // Parse comments
            if (isset($data['staff_comments']) && $data['staff_comments']) {
                $data['parsed_staff_comments'] = parse($data['staff_comments']);
            }

            $submission->update([
                'staff_comments'        => $data['staff_comments'],
                'parsed_staff_comments' => $data['parsed_staff_comments'],
                'staff_id'              => $user->id,
            ]);

            if (isset($data['alert_user'])) {
                Notifications::create('GALLERY_SUBMISSION_STAFF_COMMENTS', $submission->user, [
                    'sender'           => $user->name,
                    'sender_url'       => $user->url,
                    'submission_title' => $submission->title,
                    'submission_id'    => $submission->id,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Archives a submission.
     *
     * @param GallerySubmission $submission
     * @param mixed             $user
     *
     * @return bool
     */
    public function archiveSubmission($submission, $user) {
        DB::beginTransaction();

        try {
            if (!$submission) {
                throw new \Exception('Invalid submission selected.');
            }
            if ($submission->user->id != $user->id && !$user->hasPower('manage_submissions')) {
                throw new \Exception("You can't archive this submission.");
            }

            if ($user->isStaff) {
                if (!$this->logAdminAction($user, 'Archived Gallery Submission', 'Archived gallery submission '.$submission->displayName)) {
                    throw new \Exception('Failed to log admin action.');
                }
            }

            if ($submission->is_visible) {
                $submission->update(['is_visible' => 0]);
            } else {
                $submission->update(['is_visible' => 1]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes group currency evaluation for a submission.
     *
     * @param User  $user
     * @param mixed $id
     * @param mixed $data
     *
     * @return bool|GalleryFavorite
     */
    public function postValueSubmission($id, $data, $user) {
        DB::beginTransaction();

        try {
            $submission = GallerySubmission::find($id);
            // Check that the submission exists and that the user can value it
            if (!$submission) {
                throw new \Exception('Invalid submission selected.');
            }
            if (!$user->hasPower('manage_submissions')) {
                throw new \Exception("You can't evaluate this submission.");
            }

            if (!isset($data['ineligible'])) {
                // Process data and award currency for each user associated with the submission
                // First, set up CurrencyManager instance and log information
                $currencyManager = new CurrencyManager;
                $currency = Currency::find(Settings::get('group_currency'));

                $awardType = 'Gallery Submission Reward';
                $awardData = 'Received reward for gallery submission (<a href="'.$submission->url.'">#'.$submission->id.'</a>)';

                $grantedList = [];
                $awardQuantity = [];

                // Then cycle through associated users and award currency
                if (isset($data['value']['submitted'])) {
                    if (!$currencyManager->creditCurrency($user, $submission->user, $awardType, $awardData, $currency, $data['value']['submitted'][$submission->user->id])) {
                        throw new \Exception('Failed to award currency to submitting user.');
                    }

                    $grantedList[] = $submission->user;
                    $awardQuantity[] = $data['value']['submitted'][$submission->user->id];
                }

                if (isset($data['value']['collaborator'])) {
                    foreach ($submission->collaborators as $collaborator) {
                        if ($data['value']['collaborator'][$collaborator->user->id] > 0) {
                            // Double check that the submitting user isn't being awarded currency twice
                            if (isset($data['value']['submitted']) && $collaborator->user->id == $submission->user->id) {
                                throw new \Exception("Can't award currency to the submitting user twice.");
                            }

                            if (!$currencyManager->creditCurrency($user, $collaborator->user, $awardType, $awardData, $currency, $data['value']['collaborator'][$collaborator->user->id])) {
                                throw new \Exception('Failed to award currency to one or more collaborators.');
                            }

                            $grantedList[] = $collaborator->user;
                            $awardQuantity[] = $data['value']['collaborator'][$collaborator->user->id];
                        }
                    }
                }

                if (isset($data['value']['participant'])) {
                    foreach ($submission->participants as $participant) {
                        if ($data['value']['participant'][$participant->user->id] > 0) {
                            if (!$currencyManager->creditCurrency($user, $participant->user, $awardType, $awardData, $currency, $data['value']['participant'][$participant->user->id])) {
                                throw new \Exception('Failed to award currency to one or more participants.');
                            }

                            $grantedList[] = $participant->user;
                            $awardQuantity[] = $data['value']['participant'][$participant->user->id];
                        }
                    }
                }

                // Collect and json encode existing as well as new data for storage
                if (isset($submission->data['total'])) {
                    $valueData = collect([
                        'currencyData' => $submission->data['currencyData'],
                        'total'        => $submission->data['total'],
                        'value'        => $data['value'],
                        'staff'        => $user->id,
                    ])->toJson();
                } else {
                    $valueData = collect(['value' => $data['value'], 'staff' => $user->id])->toJson();
                }

                // Update the submission with the new data and mark it as processed
                $submission->update([
                    'data'      => $valueData,
                    'is_valued' => 1,
                ]);

                if (!$this->logAdminAction($user, 'Awarded Gallery Submission', 'Awarded gallery submission '.$submission->displayName)) {
                    throw new \Exception('Failed to log admin action.');
                }

                // Send a notification to each user that received a currency award
                foreach ($grantedList as $key=> $grantedUser) {
                    Notifications::create('GALLERY_SUBMISSION_VALUED', $grantedUser, [
                        'currency_quantity' => $awardQuantity[$key],
                        'currency_name'     => $currency->name,
                        'submission_title'  => $submission->title,
                        'submission_id'     => $submission->id,
                    ]);
                }
            } else {
                // Collect and json encode existing as well as new data for storage
                if (isset($submission->data['total'])) {
                    $valueData = collect([
                        'currencyData' => $submission->data['currencyData'],
                        'total'        => $submission->data['total'],
                        'ineligible'   => 1,
                        'staff'        => $user->id,
                    ])->toJson();
                } else {
                    $valueData = collect(['ineligible' => 1, 'staff' => $user->id])->toJson();
                }

                // Update the submission, including marking it as processed
                $submission->update([
                    'data'      => $valueData,
                    'is_valued' => 1,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Toggles favorite status on a submission for a user.
     *
     * @param GallerySubmission $submission
     * @param User              $user
     *
     * @return bool|GalleryFavorite
     */
    public function favoriteSubmission($submission, $user) {
        DB::beginTransaction();

        try {
            // Check that the submission can be favorited
            if (!$submission->isVisible) {
                throw new \Exception("This submission isn't visible to be favorited.");
            }
            if ($submission->user->id == $user->id || $submission->collaborators->where('user_id', $user->id)->first() != null) {
                throw new \Exception("You can't favorite your own submission!");
            }

            // Check if the user has an existing favorite, and if so, delete it
            // or else create one.
            if ($submission->favorites->where('user_id', $user->id)->first() != null) {
                $submission->favorites()->where('user_id', $user->id)->delete();
            } else {
                GalleryFavorite::create([
                    'user_id'               => $user->id,
                    'gallery_submission_id' => $submission->id,
                ]);

                if ($submission->user->id != $user->id) {
                    Notifications::create('GALLERY_SUBMISSION_FAVORITE', $submission->user, [
                        'sender_url'       => $user->url,
                        'sender'           => $user->name,
                        'submission_title' => $submission->title,
                        'submission_id'    => $submission->id,
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes rejection for a submission.
     *
     * @param GallerySubmission $submission
     * @param mixed             $user
     *
     * @return bool|GallerySubmission
     */
    public function rejectSubmission($submission, $user) {
        DB::beginTransaction();

        try {
            // Check that the submission exists and is pending
            if (!$submission) {
                throw new \Exception('Invalid submission selected.');
            }
            if ($submission->status != 'Pending') {
                throw new \Exception("This submission isn't pending.");
            }

            if (!$this->logAdminAction($user, 'Rejected Gallery Submission', 'Rejected gallery submission '.$submission->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            $submission->update(['status' => 'Rejected']);

            Notifications::create('GALLERY_SUBMISSION_REJECTED', $submission->user, [
                'submission_title' => $submission->title,
                'submission_id'    => $submission->id,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a gallery submission.
     *
     * @param array $data
     *
     * @return array
     */
    private function populateData($data) {
        // Parse any text
        if (isset($data['text']) && $data['text']) {
            $data['parsed_text'] = parse($data['text']);
        }
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        return $data;
    }

    /**
     * Processes gallery submission images.
     *
     * @param array             $data
     * @param GallerySubmission $submission
     *
     * @return array
     */
    private function processImage($data, $submission) {
        if (isset($submission->hash)) {
            unlink($submission->imagePath.'/'.$submission->imageFileName);
            unlink($submission->imagePath.'/'.$submission->thumbnailFileName);
        }
        $submission->hash = randomString(10);
        $submission->extension = config('lorekeeper.settings.gallery_images_format') ?? $data['image']->getClientOriginalExtension();

        // Save image itself
        $this->handleImage($data['image'], $submission->imagePath, $submission->imageFileName);

        $imageProperties = getimagesize($submission->imagePath.'/'.$submission->imageFileName);
        if ($imageProperties[0] > 2000 || $imageProperties[1] > 2000) {
            // For large images (in terms of dimensions),
            // use imagick instead, as it's better at handling them
            Config::set('image.driver', 'imagick');
        }

        if (config('lorekeeper.settings.gallery_images_cap') || config('lorekeeper.settings.gallery_images_format')) {
            $image = Image::make($submission->imagePath.'/'.$submission->imageFileName);

            // Scale the image if desired/necessary
            if (config('lorekeeper.settings.gallery_images_cap') && ($imageProperties[0] > config('lorekeeper.settings.gallery_images_cap') || $imageProperties[1] > config('lorekeeper.settings.gallery_images_cap'))) {
                if ($image->width() > $image->height()) {
                    // Landscape
                    $image->resize(config('lorekeeper.settings.gallery_images_cap'), null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } else {
                    // Portrait
                    $image->resize(null, config('lorekeeper.settings.gallery_images_cap'), function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
            }

            // Save the processed image
            $image->save($submission->imagePath.'/'.$submission->imageFileName, 100, config('lorekeeper.settings.gallery_images_format'));
        }

        // Process thumbnail
        Image::make($submission->imagePath.'/'.$submission->imageFileName)
            ->resize(null, config('lorekeeper.settings.masterlist_thumbnails.height'), function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save($submission->thumbnailPath.'/'.$submission->thumbnailFileName);

        return $submission;
    }

    /**
     * Processes acceptance for a submission.
     *
     * @param GallerySubmission $submission
     *
     * @return bool|GallerySubmission
     */
    private function acceptSubmission($submission) {
        DB::beginTransaction();

        try {
            // Check that the submission exists and is pending
            if (!$submission) {
                throw new \Exception('Invalid submission selected.');
            }
            if ($submission->status != 'Pending') {
                throw new \Exception("This submission isn't pending.");
            }

            $submission->update(['status' => 'Accepted']);

            // If the submission wouldn't have been automatically approved, send a notification
            if (Settings::get('gallery_submissions_require_approval') || (!Settings::get('gallery_submissions_require_approval') && $submission->collaborators->count())) {
                Notifications::create('GALLERY_SUBMISSION_ACCEPTED', $submission->user, [
                    'submission_title' => $submission->title,
                    'submission_id'    => $submission->id,
                ]);
            }

            if ($submission->characters->count()) {
                // Send a notification to included characters' owners now that the submission is accepted
                // but not for the submitting user's own characters
                foreach ($submission->characters as $character) {
                    if ($character->character->user && $character->character->user->id != $submission->user->id) {
                        Notifications::create('GALLERY_SUBMISSION_CHARACTER', $character->character->user, [
                            'sender'        => $submission->user->name,
                            'sender_url'    => $submission->user->url,
                            'character_url' => $character->character->url,
                            'character'     => isset($character->character->name) ? $character->character->fullName : $character->character->slug,
                            'submission_id' => $submission->id,
                        ]);
                    }
                }
            }

            if ($submission->participants->count()) {
                // Send a notification to participants now that the submission is accepted
                // but not for the submitting user
                foreach ($submission->participants as $participant) {
                    if ($participant->user->id != $submission->user->id) {
                        Notifications::create('GALLERY_SUBMISSION_PARTICIPANT', $participant->user, [
                            'sender_url'    => $submission->user->url,
                            'sender'        => $submission->user->name,
                            'submission_id' => $submission->id,
                        ]);
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
