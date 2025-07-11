<?php

namespace App\Models\Gallery;

use App\Facades\Settings;
use App\Models\Comment\Comment;
use App\Models\Currency\Currency;
use App\Models\Model;
use App\Models\Prompt\Prompt;
use App\Models\Submission\Submission;
use App\Models\User\User;
use App\Traits\Commentable;

class GallerySubmission extends Model {
    use Commentable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'gallery_id', 'hash', 'extension',
        'text', 'parsed_text', 'content_warning',
        'title', 'description', 'parsed_description',
        'prompt_id', 'data', 'is_visible', 'status',
        'vote_data', 'staff_id', 'is_valued',
        'staff_comments', 'parsed_staff_comments',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gallery_submissions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'      => 'array',
        'vote_data' => 'array',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'user', 'collaborators', 'prompt:id,name,prefix', 'favorites', 'comments:id,commentable_type,commentable_id,type',
    ];

    /**
     * 	The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = [
        'favorites',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for character creation.
     *
     * @var array
     */
    public static $createRules = [
        'title'       => 'required|between:3,200',
        'image'       => 'required_without:text|mimes:png,jpeg,jpg,gif,webp|max:3000',
        'text'        => 'required_without:image',
        'description' => 'nullable',
    ];

    /**
     * Validation rules for character updating.
     *
     * @var array
     */
    public static $updateRules = [
        'title'       => 'required|between:3,200',
        'description' => 'nullable',
        'image'       => 'mimes:png,jpeg,jpg,gif,webp|max:3000',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who made the submission.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the staff member who last edited the submission's comments.
     */
    public function staff() {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the collaborating users on the submission.
     */
    public function collaborators() {
        return $this->hasMany(GalleryCollaborator::class)->where('type', 'Collab');
    }

    /**
     * Get the user(s) who are related to the submission in some way.
     */
    public function participants() {
        return $this->hasMany(GalleryCollaborator::class)->where('type', '!=', 'Collab');
    }

    /**
     * Get the characters associated with the submission.
     */
    public function characters() {
        return $this->hasMany(GalleryCharacter::class);
    }

    /**
     * Get any favorites on the submission.
     */
    public function favorites() {
        return $this->hasMany(GalleryFavorite::class);
    }

    /**
     * Get the gallery this submission is in.
     */
    public function gallery() {
        return $this->belongsTo(Gallery::class);
    }

    /**
     * Get the prompt this submission is for if relevant.
     */
    public function prompt() {
        return $this->belongsTo(Prompt::class);
    }

    /**
     * Get comments made on this submission.
     */
    public function comments() {
        return $this->morphMany(Comment::class, 'commentable');
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
    public function scopePending($query) {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include submissions where all collaborators have approved.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCollaboratorApproved($query) {
        return $query->whereDoesntHave('collaborators', function ($query) {
            $query->where('has_approved', 0);
        })->orWhereDoesntHave('collaborators');
    }

    /**
     * Scope a query to only include accepted submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccepted($query) {
        return $query->where('status', 'Accepted');
    }

    /**
     * Scope a query to only include rejected submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query) {
        return $query->where('status', 'Rejected');
    }

    /**
     * Scope a query to only include submissions that require currency awards.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiresAward($query) {
        if (!Settings::get('gallery_submissions_reward_currency')) {
            return $query->whereNull('id');
        }

        return $query->where('status', 'Accepted')->whereIn('gallery_id', Gallery::where('currency_enabled', 1)->pluck('id')->toArray());
    }

    /**
     * Scope a query to only include submissions the user has either submitted or collaborated on.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserSubmissions($query, $user) {
        return $query->where('user_id', $user->id)->orWhereHas('collaborators', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('type', 'Collab');
        });
    }

    /**
     * Scope a query to only include submissions visible within the gallery.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('manage_submissions')) {
            return $query->where('status', 'Accepted');
        }

        return $query->where('status', 'Accepted')->where('is_visible', 1);
    }

    /**
     * Scope a query to sort submissions by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query, $reverse = false) {
        return $query->orderBy('id', $reverse ? 'ASC' : 'DESC');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/gallery/'.floor($this->id / 1000);
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'_'.$this->hash.'.'.$this->extension;
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!isset($this->hash)) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the file name of the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailFileNameAttribute() {
        return $this->id.'_'.$this->hash.'_th.'.$this->extension;
    }

    /**
     * Gets the path to the file directory containing the model's thumbnail image.
     *
     * @return string
     */
    public function getThumbnailPathAttribute() {
        return $this->imagePath;
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getThumbnailUrlAttribute() {
        if (!isset($this->hash)) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->thumbnailFileName);
    }

    /**
     * Get the title of the submission, with prefix.
     *
     * @return string
     */
    public function getDisplayTitleAttribute() {
        return $this->prefix.$this->attributes['title'];
    }

    /**
     * Get the display name of the submission.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'">'.$this->displayTitle.'</a>';
    }

    /**
     * Get the viewing URL of the submission.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('gallery/view/'.$this->id);
    }

    /**
     * Get the prefix for a submission.
     *
     * @return string
     */
    public function getPrefixAttribute() {
        $prefixList = [];
        if ($this->promptSubmissions->count()) {
            foreach ($this->prompts as $prompt) {
                isset($prompt->prefix) ? ($prefixList[] = $prompt->prefix) : null;
            }
        } elseif (isset($this->prompt_id)) {
            isset($this->prompt->prefix) ? $prefixList[] = $this->prompt->prefix : null;
        }
        foreach ($this->participants as $participant) {
            switch ($participant->type) {
                case 'Collab':
                    $prefixList[] = 'Collab';
                    break;
                case 'Trade':
                    $prefixList[] = 'Trade';
                    break;
                case 'Gift':
                    $prefixList[] = 'Gift';
                    break;
                case 'Comm':
                    $prefixList[] = 'Comm';
                    break;
                case 'Comm (Currency)':
                    $currencyName = Currency::find(Settings::get('group_currency'))->abbreviation ? Currency::find(Settings::get('group_currency'))->abbreviation : Currency::find(Settings::get('group_currency'))->name;

                    $prefixList[] = 'Comm ('.$currencyName.')';
                    break;
            }
        }
        if ($prefixList != null) {
            return '['.implode(' : ', array_unique($prefixList)).'] ';
        }

        return null;
    }

    /**
     * Get the internal processing URL of the submission.
     *
     * @return string
     */
    public function getQueueUrlAttribute() {
        return url('gallery/queue/'.$this->id);
    }

    /**
     * Get whether or not the submission is generally viewable.
     *
     * @return bool
     */
    public function getIsVisibleAttribute() {
        if ($this->attributes['is_visible'] && $this->status == 'Accepted') {
            return true;
        }
    }

    /**
     * Get the users responsible for the submission (submitting user or collaborators).
     *
     * @return string
     */
    public function getCreditsAttribute() {
        if ($this->collaborators->count()) {
            foreach ($this->collaborators as $collaborator) {
                $collaboratorList[] = $collaborator->user->displayName;
            }

            return implode(', ', $collaboratorList);
        } else {
            return $this->user->displayName;
        }
    }

    /**
     * Get the users responsible for the submission (submitting user or collaborators).
     *
     * @return string
     */
    public function getCreditsPlainAttribute() {
        if ($this->collaborators->count()) {
            foreach ($this->collaborators as $collaborator) {
                $collaboratorList[] = $collaborator->user->name;
            }

            return implode(', ', $collaboratorList);
        } else {
            return $this->user->name;
        }
    }

    /**
     * Checks if all of a submission's collaborators have approved or no.
     *
     * @return string
     */
    public function getCollaboratorApprovalAttribute() {
        if ($this->collaborators->where('has_approved', 0)->count()) {
            return false;
        }

        return true;
    }

    /**
     * Gets prompt submissions associated with this gallery submission.
     *
     * @return array
     */
    public function getPromptSubmissionsAttribute() {
        // Only returns submissions which are viewable to everyone,
        // but given that this is for the sake of public display, that's fine
        return Submission::viewable()->whereNotNull('prompt_id')->where('url', 'like', '%'.request()->getHost().'/gallery/view/'.$this->id)->get();
    }

    /**
     * Gets prompts associated with this gallery submission.
     *
     * @return array
     */
    public function getPromptsAttribute() {
        // Only returns submissions which are viewable to everyone,
        // but given that this is for the sake of public display, that's fine
        return Prompt::whereIn('id', $this->promptSubmissions->pluck('prompt_id'))->get();
    }

    /**
     * Gets the excerpt of text for a literature submission.
     *
     * @return string
     */
    public function getExcerptAttribute() {
        if (!isset($this->parsed_text)) {
            return null;
        } else {
            return strip_tags(substr($this->parsed_text, 0, 500)).(strlen($this->parsed_text) > 500 ? '...' : '');
        }
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

     **********************************************************************************************/

    /**
     * Gets the voting data of the gallery submission and performs preliminary processing.
     *
     * @param bool $withUsers
     *
     * @return array
     */
    public function getVoteData($withUsers = 0) {
        $voteData['raw'] = $this->vote_data;

        // Only query users if necessary, and condense to one query per submission
        if ($withUsers) {
            $users = User::whereIn('id', array_keys($voteData['raw']))->select('id', 'name', 'rank_id')->get();
        } else {
            $users = null;
        }

        $voteData['raw'] = collect($voteData['raw'])->mapWithKeys(function ($vote, $id) use ($users) {
            return [$id => [
                'vote' => $vote,
                'user' => $users ? $users->where('id', $id)->first() : $id,
            ]];
        });

        // Tally approve/reject sums for ease
        $voteData['approve'] = $voteData['reject'] = 0;
        foreach ($voteData['raw'] as $vote) {
            switch ($vote['vote']) {
                case 1:
                    $voteData['reject'] += 1;
                    break;
                case 2:
                    $voteData['approve'] += 1;
                    break;
            }
        }

        return $voteData;
    }
}
