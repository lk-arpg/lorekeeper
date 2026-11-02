<?php

namespace App\Models\Comment;

use App\Events\CommentCreated;
use App\Events\CommentDeleted;
use App\Events\CommentUpdated;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model {
    use SoftDeletes;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'commenter',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'comment', 'approved', 'guest_name', 'guest_email', 'is_featured', 'type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'approved' => 'boolean',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => CommentCreated::class,
        'updated' => CommentUpdated::class,
        'deleted' => CommentDeleted::class,
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

     **********************************************************************************************/

    /**
     * The user who posted the comment.
     */
    public function commenter() {
        return $this->morphTo();
    }

    /**
     * The model that was commented upon.
     */
    public function commentable() {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Returns all comments that this comment is the parent of.
     */
    public function children() {
        return $this->hasMany(self::class, 'child_id')->withTrashed();
    }

    /**
     * Returns the comment to which this comment belongs to.
     */
    public function parent() {
        return $this->belongsTo(self::class, 'child_id')->withTrashed();
    }

    /**
     * Gets the likes for this comment.
     */
    public function likes() {
        return $this->hasMany(CommentLike::class);
    }

    /**
     * Get the edit history of the comment.
     */
    public function edits() {
        return $this->hasMany(CommentEdit::class)->orderBy('created_at', 'desc');
    }

    /**********************************************************************************************

        ATTRIBUTES

     **********************************************************************************************/

    /**
     * Gets / Creates permalink for comments - allows user to go directly to comment.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('comment/'.$this->id);
    }

    /**
     * Gets top comment.
     *
     * @return string
     */
    public function getTopCommentAttribute() {
        if (!$this->parent) {
            return $this;
        } else {
            return $this->parent->topComment;
        }
    }

    /**
     * Gets the end of a comment's thread.
     *
     * @return Comment
     */
    public function getEndOfThreadAttribute() {
        if ($this->children->count() > 0) {
            return $this->children->sortByDesc('created_at')->first()->endOfThread;
        } else {
            return $this;
        }
    }

    /**
     * Returns the comment contents but with links in clickable format.
     *
     * @return string
     */
    public function getCommentAttribute() {
        if (config('lorekeeper.settings.wysiwyg_comments')) {
            return preg_replace_callback(
                '/(?<!href=")(?<!src=")(?<!\()(https?:\/\/[^\s<]+)/',
                function ($matches) {
                    $url = $matches[1];
                    $parsedUrl = parse_url($url);
                    $domain = $parsedUrl['host'];

                    return '<a href="'.$url.'" target="_blank">'.$domain.'</a>';
                },
                $this->attributes['comment']
            );
        }

        return preg_replace_callback(
            '/(?<!\()(https?:\/\/[^\s]+)/',
            function ($matches) {
                $url = $matches[1];
                $parsedUrl = parse_url($url);
                $domain = $parsedUrl['host'];

                return '['.$domain.']('.$url.')';
            },
            $this->attributes['comment']
        );
    }
}
