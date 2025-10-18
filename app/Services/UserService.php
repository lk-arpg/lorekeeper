<?php namespace App\Services;

use App\Services\Service;

use DB;
use Auth;
use File;
use Image;
use Carbon\Carbon;

use App\Models\User\User;
use App\Models\Rank\Rank;
use App\Models\Character\CharacterTransfer;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Submission\Submission;
use App\Models\Gallery\GallerySubmission;
use App\Models\User\UserUpdateLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Queue\QueueSubmission;

use App\Services\SubmissionManager;
use App\Services\GalleryManager;
use App\Services\CharacterManager;
use App\Models\Trade;
use App\Services\QueueSubmissionManager;

class UserService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | User Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of users.
    |
    */

    /**
     * Create a user.
     *
     * @param  array  $data
     * @return \App\Models\User\User
     */
    public function createUser($data)
    {
        // If the rank is not given, create a user with the lowest existing rank.
        if(!isset($data['rank_id'])) $data['rank_id'] = Rank::orderBy('sort')->first()->id;

        // Make birthday into format we can store
        $date = $data['dob']['day']."-".$data['dob']['month']."-".$data['dob']['year'];
        $formatDate = carbon::parse($date);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'rank_id' => $data['rank_id'],
            'password' => Hash::make($data['password']),
            'birthday' => $formatDate,
        ]);
        $user->settings()->create([
            'user_id' => $user->id,
        ]);
        $user->profile()->create([
            'user_id' => $user->id
        ]);

        return $user;
    }

    /**
     * Updates a user. Used in modifying the admin user on the command line.
     *
     * @param  array  $data
     * @return \App\Models\User\User
     */
    public function updateUser($data)
    {
        $user = User::find($data['id']);
        if(isset($data['password'])) $data['password'] = Hash::make($data['password']);
        if($user) $user->update($data);

        return $user;
    }

    /**
     * Updates the user's password.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function updatePassword($data, $user)
    {

        DB::beginTransaction();

        try {
            if(!Hash::check($data['old_password'], $user->password)) throw new \Exception("Please enter your old password.");
            if(Hash::make($data['new_password']) == $user->password) throw new \Exception("Please enter a different password.");

            $user->password = Hash::make($data['new_password']);
            $user->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates the user's email and resends a verification email.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function updateEmail($data, $user)
    {
        $user->email = $data['email'];
        $user->email_verified_at = null;
        $user->save();

        $user->sendEmailVerificationNotification();

        return true;
    }

    /**
     * Updates user's birthday
     */
    public function updateBirthday($data, $user)
    {
        $user->birthday = $data;
        $user->save();

        return true;
    }

    /**
     * Updates user's birthday setting
     */
    public function updateDOB($data, $user)
    {
        $user->settings->birthday_setting = $data;
        $user->settings->save();

        return true;
    }

    /**
     * Updates the user's avatar.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function updateAvatar($avatar, $user)
    {
        DB::beginTransaction();

        try {
            if(!$avatar) throw new \Exception ("Please upload a file.");
            $filename = $user->id . '.' . $avatar->getClientOriginalExtension();

            if ($user->avatar !== 'default.jpg') {
                $file = 'images/avatars/' . $user->avatar;
                //$destinationPath = 'uploads/' . $id . '/';

                if (File::exists($file)) {
                    if(!unlink($file)) throw new \Exception("Failed to unlink old avatar.");
                }
            }

            // Checks if uploaded file is a GIF
            if ($avatar->getClientOriginalExtension() == 'gif') {

                if(!copy($avatar, $file)) throw new \Exception("Failed to copy file.");
                if(!$file->move( public_path('images/avatars', $filename))) throw new \Exception("Failed to move file.");
                if(!$avatar->move( public_path('images/avatars', $filename))) throw new \Exception("Failed to move file.");

            }

            else {
                if(!Image::make($avatar)->resize(150, 150)->save( public_path('images/avatars/' . $filename)))
                throw new \Exception("Failed to process avatar.");
            }

            $user->avatar = $filename;
            $user->save();

            return $this->commitReturn($avatar);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Bans a user.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\User\User  $staff
     * @return bool
     */
    public function ban($data, $user, $staff)
    {
        DB::beginTransaction();

        try {
            if(!$user->is_banned) {
                // New ban (not just editing the reason), clear all their engagements

                // 1. Character transfers
                $characterManager = new CharacterManager;
                $transfers = CharacterTransfer::where(function($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->where('status', 'Pending')->get();
                foreach($transfers as $transfer)
                    $characterManager->processTransferQueue(['transfer' => $transfer, 'action' => 'Reject', 'reason' => ($transfer->sender_id == $user->id ? 'Sender' : 'Recipient') . ' has been banned from site activity.'], $staff);

                // 2. Submissions and claims
                $submissionManager = new SubmissionManager;
                $submissions = Submission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach($submissions as $submission)
                    $submissionManager->rejectSubmission(['submission' => $submission, 'staff_comments' => 'User has been banned from site activity.']);

                // 3. Gallery Submissions
                $galleryManager = new GalleryManager;
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach($gallerySubmissions as $submission) {
                    $galleryManager->rejectSubmission($submission);
                    $galleryManager->postStaffComments($submission->id, ['staff_comments' => 'User has been banned from site activity.'], $staff);
                }
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Accepted')->get();
                foreach($gallerySubmissions as $submission)
                    $submission->update(['is_visible' => 0]);

                // 4. Design approvals
                $requests = CharacterDesignUpdate::where('user_id', $user->id)->where(function($query) {
                    $query->where('status', 'Pending')->orWhere('status', 'Draft');
                })->get();
                foreach($requests as $request)
                    $characterManager->rejectRequest(['staff_comments' => 'User has been banned from site activity.'], $request, $staff, true);

                // 5. Trades
                $tradeManager = new TradeManager;
                $trades = Trade::where(function($query) {
                    $query->where('status', 'Open')->orWhere('status', 'Pending');
                })->where(function($query) use ($user) {
                    $query->where('sender_id', $user->id)->where('recipient_id', $user->id);
                })->get();
                foreach($trades as $trade)
                    $tradeManager->rejectTrade(['trade' => $trade, 'reason' => 'User has been banned from site activity.'], $staff);

                    // 6. queues
                $qsubmissionManager = new QueueSubmissionManager;
                $qsubmissions = QueueSubmission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach($qsubmissions as $qsubmission)
                    $qsubmissionManager->rejectSubmission(['submission' => $qsubmission, 'staff_comments' => 'User has been banned from site activity.'], $staff);

                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => json_encode(['is_banned' => 'Yes', 'ban_reason' => isset($data['ban_reason']) ? $data['ban_reason'] : null]), 'type' => 'Ban']);

                $user->settings->banned_at = Carbon::now();

                $user->is_banned = 1;
                $user->rank_id = Rank::orderBy('sort')->first()->id;
                $user->save();
            }
            else {
                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => json_encode(['ban_reason' => isset($data['ban_reason']) ? $data['ban_reason'] : null]), 'type' => 'Ban Update']);
            }

            $user->settings->ban_reason = isset($data['ban_reason']) && $data['ban_reason'] ? $data['ban_reason'] : null;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Unbans a user.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\User\User  $staff
     * @return bool
     */
    public function unban($user, $staff)
    {
        DB::beginTransaction();

        try {
            if($user->is_banned) {
                $user->is_banned = 0;
                $user->save();

                $user->settings->ban_reason = null;
                $user->settings->banned_at = null;
                $user->settings->save();
                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => json_encode(['is_banned' => 'No']), 'type' => 'Unban']);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
