<?php

namespace App\Models\Comment;

use App\Models\Model;
use App\Models\User\User;

class CommentEdit extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'comment_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comment_edits';

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

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the comment.
     */
    public function comment() {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the user.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
