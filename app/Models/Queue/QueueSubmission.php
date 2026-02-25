<?php

namespace App\Models\Queue;

use App\Models\Model;
use App\Models\User\User;
use Carbon\Carbon;

class QueueSubmission extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'queue_id', 'user_id', 'staff_id', 'url',
        'comments', 'parsed_comments', 'staff_comments', 'parsed_staff_comments',
        'status', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'queue_submissions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for submission creation.
     *
     * @var array
     */
    public static $createRules = [
        'url' => 'nullable|url',
    ];

    /**
     * Validation rules for submission updating.
     *
     * @var array
     */
    public static $updateRules = [
        'url' => 'nullable|url',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the queue this submission is for.
     */
    public function queue() {
        return $this->belongsTo(Queue::class, 'queue_id');
    }

    /**
     * Get the user who made the submission.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the staff who processed the submission.
     */
    public function staff() {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the characters attached to the submission.
     */
    public function characters() {
        return $this->hasMany(QueueSubmissionCharacter::class, 'queue_submission_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include pending submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include drafted submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDrafts($query) {
        return $query->where('status', 'Drafts');
    }

    /**
     * Scope a query to only include viewable submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeViewable($query, $user = null) {
        $forbiddenSubmissions = $this
            ->whereHas('queue', function ($q) {
                $q->where('hide_submissions', 1)->whereNotNull('end_at')->where('end_at', '>', Carbon::now());
            })
            ->orWhereHas('queue', function ($q) {
                $q->where('hide_submissions', 2);
            })
            ->orWhere('status', '!=', 'Approved')->pluck('id')->toArray();

        if ($user && $user->hasPower('manage_submissions')) {
            return $query;
        } else {
            return $query->where(function ($query) use ($user, $forbiddenSubmissions) {
                if ($user) {
                    $query->whereNotIn('id', $forbiddenSubmissions)->orWhere('user_id', $user->id);
                } else {
                    $query->whereNotIn('id', $forbiddenSubmissions);
                }
            });
        }
    }

    /**
     * Scope a query to sort submissions oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to sort submissions by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the inventory of the user for selection.
     *
     * @param mixed $user
     *
     * @return array
     */
    public function getInventory($user) {
        return $this->data && isset($this->data['user']['user_items']) ? $this->data['user']['user_items'] : [];
    }

    /**
     * Gets the currencies of the given user for selection.
     *
     * @param User $user
     *
     * @return array
     */
    public function getCurrencies($user) {
        return $this->data && isset($this->data['user']) && isset($this->data['user']['currencies']) ? $this->data['user']['currencies'] : [];
    }

    /**
     * Get the viewing URL of the submission/claim.
     *
     * @return string
     */
    public function getViewUrlAttribute() {
        return url('queue-submissions/view/'.$this->id);
    }

    /**
     * Get the admin URL (for processing purposes) of the submission/claim.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/queue-submissions/edit/'.$this->id);
    }

    /**
     * Scope a query to only include user's logs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $queue
     * @param mixed                                 $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSubmitted($query, $queue, $user) {
        return $query->where('queue_id', $queue)->where('user_id', $user)->where('status', '=', 'Approved')->orWhere('queue_id', $queue)->where('user_id', $user)->where('status', '=', 'Pending');
    }

    /**
     * Scope a query to only include user's logs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $queue
     * @param mixed                                 $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query, $queue, $user) {
        return $query->where('queue_id', $queue)->where('user_id', $user)->where('status', '=', 'Pending')->orWhere('queue_id', $queue)->where('user_id', $user)->where('status', '=', 'Draft');
    }

    /**
     * Get the rewards for the submission/claim.
     *
     * @return array
     */
    public function getRewardsAttribute() {
        if (isset($this->data['rewards'])) {
            $assets = parseAssetData($this->data['rewards']);
        } else {
            $assets = parseAssetData($this->data);
        }
        $rewards = [];
        foreach ($assets as $type => $a) {
            $class = getAssetModelString($type, false);
            foreach ($a as $id => $asset) {
                $rewards[] = (object) [
                    'rewardable_type' => $class,
                    'rewardable_id'   => $id,
                    'quantity'        => $asset['quantity'],
                ];
            }
        }

        return $rewards;
    }
}
