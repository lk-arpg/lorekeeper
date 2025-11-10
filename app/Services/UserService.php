<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterTransfer;
use App\Models\Gallery\GallerySubmission;
use App\Models\Invitation;
use App\Models\Rank\Rank;
use App\Models\Submission\Submission;
use App\Models\Trade\Trade;
use App\Models\User\User;
use App\Models\User\UserUpdateLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class UserService extends Service {
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
     * @param array $data
     *
     * @return User
     */
    public function createUser($data) {
        // If the rank is not given, create a user with the lowest existing rank.
        if (!isset($data['rank_id'])) {
            $data['rank_id'] = Rank::orderBy('sort')->first()->id;
        }

        // Make birthday into format we can store
        $formatDate = Carbon::parse($data['dob']);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'] ?? null,
            'rank_id'   => $data['rank_id'],
            'password'  => isset($data['password']) ? Hash::make($data['password']) : null,
            'birthday'  => $formatDate,
            'has_alias' => $data['has_alias'] ?? false,
            // Verify the email if we're logging them in with their social
            'email_verified_at' => (!isset($data['password']) && !isset($data['email'])) ? now() : null,
        ]);
        $user->settings()->create([
            'user_id' => $user->id,
        ]);
        $user->profile()->create([
            'user_id' => $user->id,
        ]);

        return $user;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param mixed $socialite
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data, $socialite = false) {
        return Validator::make($data, [
            'name'      => ['required', 'string', 'min:3', 'max:25', 'alpha_dash', 'unique:users'],
            'email'     => ($socialite ? [] : ['required']) + ['string', 'email', 'max:255', 'unique:users'],
            'agreement' => ['required', 'accepted'],
            'password'  => ($socialite ? [] : ['required']) + ['string', 'min:8', 'confirmed'],
            'dob'       => [
                'required', function ($attribute, $value, $fail) {
                    $formatDate = Carbon::createFromFormat('Y-m-d', $value);
                    $now = Carbon::now();
                    if ($formatDate->diffInYears($now) < 13) {
                        $fail('You must be 13 or older to access this site.');
                    }
                },
            ],
            'code'                 => ['string', function ($attribute, $value, $fail) {
                if (!Settings::get('is_registration_open')) {
                    if (!$value) {
                        $fail('An invitation code is required to register an account.');
                    }
                    $invitation = Invitation::where('code', $value)->whereNull('recipient_id')->first();
                    if (!$invitation) {
                        $fail('Invalid code entered.');
                    }
                }
            },
            ],
        ] + (config('app.env') == 'production' && config('lorekeeper.extensions.use_recaptcha') ? [
            'g-recaptcha-response' => 'required|recaptchav3:register,0.5',
        ] : []));
    }

    /**
     * Updates a user. Used in modifying the admin user on the command line.
     *
     * @param array $data
     *
     * @return User
     */
    public function updateUser($data) {
        $user = User::find($data['id']);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        if ($user) {
            $user->update($data);
        }

        return $user;
    }

    /**
     * Updates the user's password.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function updatePassword($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($user->password) && !Hash::check($data['old_password'], $user->password)) {
                throw new \Exception('Please enter your old password.');
            }
            if (Hash::make($data['new_password']) == $user->password) {
                throw new \Exception('Please enter a different password.');
            }

            $user->password = Hash::make($data['new_password']);
            $user->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates the user's email and resends a verification email.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function updateEmail($data, $user) {
        $user->email = $data['email'];
        $user->email_verified_at = null;
        $user->save();

        $user->sendEmailVerificationNotification();

        return true;
    }

    /**
     * Updates user's birthday.
     *
     * @param mixed $data
     * @param mixed $user
     */
    public function updateBirthday($data, $user) {
        DB::beginTransaction();

        try {
            $user->birthday = $data;
            $user->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates user's birthday setting.
     *
     * @param mixed $data
     * @param mixed $user
     */
    public function updateBirthdayVisibilitySetting($data, $user) {
        DB::beginTransaction();

        try {
            $user->settings->birthday_setting = $data;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Confirms a user's two-factor auth.
     *
     * @param string           $code
     * @param array            $data
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function confirmTwoFactor($code, $data, $user) {
        DB::beginTransaction();

        try {
            if (app(TwoFactorAuthenticationProvider::class)->verify(decrypt($data['two_factor_secret']), $code['code'])) {
                $user->forceFill([
                    'two_factor_secret'         => $data['two_factor_secret'],
                    'two_factor_recovery_codes' => $data['two_factor_recovery_codes'],
                ])->save();
            } else {
                throw new \Exception('Provided code was invalid.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Disables a user's two-factor auth.
     *
     * @param string           $code
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function disableTwoFactor($code, $user) {
        DB::beginTransaction();

        try {
            if (app(TwoFactorAuthenticationProvider::class)->verify(decrypt($user->two_factor_secret), $code['code'])) {
                $user->forceFill([
                    'two_factor_secret'         => null,
                    'two_factor_recovery_codes' => null,
                ])->save();
            } else {
                throw new \Exception('Provided code was invalid.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates user's warning visibility setting.
     *
     * @param mixed $data
     * @param mixed $user
     *
     * @return bool
     */
    public function updateContentWarningVisibility($data, $user) {
        DB::beginTransaction();

        try {
            $user->settings->content_warning_visibility = $data;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates user's profile comment setting.
     *
     * @param mixed $data
     * @param mixed $user
     *
     * @return bool
     */
    public function updateProfileCommentSetting($data, $user) {
        DB::beginTransaction();

        try {
            $user->settings->allow_profile_comments = $data ?? 0;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates the user's avatar.
     *
     * @param User  $user
     * @param mixed $data
     *
     * @return bool
     */
    public function updateAvatar($data, $user) {
        DB::beginTransaction();

        try {
            $avatar = $data['avatar'];
            if (!$avatar) {
                throw new \Exception('Please upload a file.');
            }
            $filename = $user->id.'.'.$avatar->getClientOriginalExtension();

            if ($user->avatar != 'default.jpg') {
                $file = 'images/avatars/'.$user->avatar;
                // $destinationPath = 'uploads/' . $id . '/';

                if (File::exists($file)) {
                    if (!unlink($file)) {
                        throw new \Exception('Failed to unlink old avatar.');
                    }
                }
            }

            // Checks if uploaded file is a GIF
            if ($avatar->getClientOriginalExtension() == 'gif') {
                if (!$avatar->move(public_path('images/avatars'), $filename)) {
                    throw new \Exception('Failed to move file.');
                }
            } else {
                // crop image first
                $cropWidth = $data['x1'] - $data['x0'];
                $cropHeight = $data['y1'] - $data['y0'];

                $image = Image::make($avatar);
                $image->crop($cropWidth, $cropHeight, $data['x0'], $data['y0']);
                if (!$image->resize(150, 150)->save(public_path('images/avatars/'.$filename))) {
                    throw new \Exception('Failed to process avatar.');
                }
            }

            $user->avatar = $filename;
            $user->save();

            return $this->commitReturn($avatar);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a user's username.
     *
     * @param string $username
     * @param User   $user
     *
     * @return bool
     */
    public function updateUsername($username, $user) {
        DB::beginTransaction();

        try {
            if (!config('lorekeeper.settings.allow_username_changes')) {
                throw new \Exception('Username changes are currently disabled.');
            }
            if (!$username) {
                throw new \Exception('Please enter a username.');
            }
            if (strlen($username) < 3 || strlen($username) > 25) {
                throw new \Exception('Username must be between 3 and 25 characters.');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                throw new \Exception('Username must only contain letters, numbers, and underscores.');
            }
            if ($username == $user->name) {
                throw new \Exception('Username cannot be the same as your current username.');
            }
            if (User::where('name', $username)->where('id', '!=', $user->id)->first()) {
                throw new \Exception('Username already taken.');
            }
            // check if there is a cooldown
            if (config('lorekeeper.settings.username_change_cooldown')) {
                // these logs are different to the ones in the admin panel
                // different type
                $last_change = UserUpdateLog::where('user_id', $user->id)->where('type', 'Username Change')->orderBy('created_at', 'desc')->first();
                if ($last_change && $last_change->created_at->diffInDays(Carbon::now()) < config('lorekeeper.settings.username_change_cooldown')) {
                    throw new \Exception('You must wait '
                        .config('lorekeeper.settings.username_change_cooldown') - $last_change->created_at->diffInDays(Carbon::now()).
                    ' days before changing your username again.');
                }
            }

            // create log
            UserUpdateLog::create([
                'staff_id' => null,
                'user_id'  => $user->id,
                'data'     => ['old_name' => $user->name, 'new_name' => $username],
                'type'     => 'Username Change',
            ]);

            $user->name = $username;
            $user->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Bans a user.
     *
     * @param array $data
     * @param User  $user
     * @param User  $staff
     *
     * @return bool
     */
    public function ban($data, $user, $staff) {
        DB::beginTransaction();

        try {
            if (!$user->is_banned) {
                // New ban (not just editing the reason), clear all their engagements
                if (!$this->logAdminAction($staff, 'Banned User', 'Banned '.$user->displayname)) {
                    throw new \Exception('Failed to log admin action.');
                }

                // 1. Character transfers
                $characterManager = new CharacterManager;
                $transfers = CharacterTransfer::where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->where('status', 'Pending')->get();
                foreach ($transfers as $transfer) {
                    $characterManager->processTransferQueue(['transfer' => $transfer, 'action' => 'Reject', 'reason' => ($transfer->sender_id == $user->id ? 'Sender' : 'Recipient').' has been banned from site activity.'], $staff);
                }

                // 2. Submissions and claims
                $submissionManager = new SubmissionManager;
                $submissions = Submission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach ($submissions as $submission) {
                    $submissionManager->rejectSubmission(['submission' => $submission, 'staff_comments' => 'User has been banned from site activity.'], $staff);
                }

                // 3. Gallery Submissions
                $galleryManager = new GalleryManager;
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach ($gallerySubmissions as $submission) {
                    $galleryManager->rejectSubmission($submission, $staff);
                    $galleryManager->postStaffComments($submission->id, ['staff_comments' => 'User has been banned from site activity.'], $staff);
                }
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Accepted')->get();
                foreach ($gallerySubmissions as $submission) {
                    $submission->update(['is_visible' => 0]);
                }

                // 4. Design approvals
                $requests = CharacterDesignUpdate::where('user_id', $user->id)->where(function ($query) {
                    $query->where('status', 'Pending')->orWhere('status', 'Draft');
                })->get();
                foreach ($requests as $request) {
                    (new DesignUpdateManager)->rejectRequest(['staff_comments' => 'User has been banned from site activity.'], $request, $staff, true);
                }

                // 5. Trades
                $tradeManager = new TradeManager;
                $trades = Trade::where(function ($query) {
                    $query->where('status', 'Open')->orWhere('status', 'Pending');
                })->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->where('recipient_id', $user->id);
                })->get();
                foreach ($trades as $trade) {
                    $tradeManager->rejectTrade(['trade' => $trade, 'reason' => 'User has been banned from site activity.'], $staff);
                }

                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => ['is_banned' => 'Yes', 'ban_reason' => $data['ban_reason'] ?? null], 'type' => 'Ban']);

                $user->settings->banned_at = Carbon::now();

                $user->is_banned = 1;
                $user->rank_id = Rank::orderBy('sort')->first()->id;
                $user->save();
            } else {
                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => ['ban_reason' => $data['ban_reason'] ?? null], 'type' => 'Ban Update']);
            }

            $user->settings->ban_reason = isset($data['ban_reason']) && $data['ban_reason'] ? $data['ban_reason'] : null;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Unbans a user.
     *
     * @param User $user
     * @param User $staff
     *
     * @return bool
     */
    public function unban($user, $staff) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($staff, 'Unbanned User', 'Unbanned '.$user->displayname)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($user->is_banned) {
                $user->is_banned = 0;
                $user->save();

                $user->settings->ban_reason = null;
                $user->settings->banned_at = null;
                $user->settings->save();
                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => ['is_banned' => 'No'], 'type' => 'Unban']);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deactivates a user.
     *
     * @param array $data
     * @param User  $user
     * @param User  $staff
     *
     * @return bool
     */
    public function deactivate($data, $user, $staff = null) {
        DB::beginTransaction();

        try {
            if (!$staff) {
                $staff = $user;
            }
            if (!$user->is_deactivated) {
                // New deactivation (not just editing the reason), clear all their engagements

                // 1. Character transfers
                $characterManager = new CharacterManager;
                $transfers = CharacterTransfer::where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->where('status', 'Pending')->get();
                foreach ($transfers as $transfer) {
                    $characterManager->processTransferQueue(['transfer' => $transfer, 'action' => 'Reject', 'reason' => ($transfer->sender_id == $user->id ? 'Sender' : 'Recipient').'\'s account was deactivated.'], ($staff ? $staff : $user));
                }

                // 2. Submissions and claims
                $submissionManager = new SubmissionManager;
                $submissions = Submission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach ($submissions as $submission) {
                    $submissionManager->rejectSubmission(['submission' => $submission, 'staff_comments' => 'User\'s account was deactivated.'], $staff);
                }

                // 3. Gallery Submissions
                $galleryManager = new GalleryManager;
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach ($gallerySubmissions as $submission) {
                    $galleryManager->rejectSubmission($submission, $staff);
                    $galleryManager->postStaffComments($submission->id, ['staff_comments' => 'User\'s account was deactivated.'], $staff);
                }
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Accepted')->get();
                foreach ($gallerySubmissions as $submission) {
                    $submission->update(['is_visible' => 0]);
                }

                // 4. Design approvals
                $requests = CharacterDesignUpdate::where('user_id', $user->id)->where(function ($query) {
                    $query->where('status', 'Pending')->orWhere('status', 'Draft');
                })->get();
                foreach ($requests as $request) {
                    (new DesignUpdateManager)->rejectRequest(['staff_comments' => 'User\'s account was deactivated.'], $request, $staff, true);
                }

                // 5. Trades
                $tradeManager = new TradeManager;
                $trades = Trade::where(function ($query) {
                    $query->where('status', 'Open')->orWhere('status', 'Pending');
                })->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->where('recipient_id', $user->id);
                })->get();
                foreach ($trades as $trade) {
                    $tradeManager->rejectTrade(['trade' => $trade, 'reason' => 'User\'s account was deactivated.'], $staff);
                }

                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => ['is_deactivated' => 'Yes', 'deactivate_reason' => $data['deactivate_reason'] ?? null], 'type' => 'Deactivation']);

                $user->settings->deactivated_at = Carbon::now();

                $user->is_deactivated = 1;
                $user->deactivater_id = $staff->id;
                $user->rank_id = Rank::orderBy('sort')->first()->id;
                $user->save();

                Notifications::create('USER_DEACTIVATED', User::find(Settings::get('admin_user')), [
                    'user_url'   => $user->url,
                    'user_name'  => $user->name,
                    'staff_url'  => $staff->url,
                    'staff_name' => $staff->name,
                ]);
            } else {
                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => ['deactivate_reason' => $data['deactivate_reason'] ?? null], 'type' => 'Deactivation Update']);
            }

            $user->settings->deactivate_reason = isset($data['deactivate_reason']) && $data['deactivate_reason'] ? $data['deactivate_reason'] : null;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Reactivates a user account.
     *
     * @param User $user
     * @param User $staff
     *
     * @return bool
     */
    public function reactivate($user, $staff = null) {
        DB::beginTransaction();

        try {
            if (!$staff) {
                $staff = $user;
            }
            if ($user->is_deactivated) {
                $user->is_deactivated = 0;
                $user->deactivater_id = null;
                $user->save();

                $user->settings->deactivate_reason = null;
                $user->settings->deactivated_at = null;
                $user->settings->save();
                UserUpdateLog::create(['staff_id' => $staff ? $staff->id : $user->id, 'user_id' => $user->id, 'data' => ['is_deactivated' => 'No'], 'type' => 'Reactivation']);
            }

            Notifications::create('USER_REACTIVATED', User::find(Settings::get('admin_user')), [
                'user_url'   => $user->url,
                'user_name'  => ucfirst($user->name),
                'staff_url'  => $staff->url,
                'staff_name' => $staff->name,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
