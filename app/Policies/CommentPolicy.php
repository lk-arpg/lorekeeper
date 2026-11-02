<?php

namespace App\Policies;

use App\Facades\Settings;
use App\Models\Comment\Comment;
use Illuminate\Support\Facades\Auth;

class CommentPolicy {
    /**
     * Can user create the comment.
     *
     * @param mixed $user
     */
    public function create($user): bool {
        return true;
    }

    /**
     * Can user delete the comment.
     *
     * @param mixed $user
     */
    public function delete($user, Comment $comment): bool {
        if (Auth::user()->isStaff) {
            return true;
        } elseif ($comment->commentable_type == 'App\Models\User\UserProfile' && $comment->commentable_id == $user->id && Settings::get('allow_users_to_delete_profile_comments')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Can user update the comment.
     *
     * @param mixed $user
     */
    public function update($user, Comment $comment): bool {
        return $user->getKey() == $comment->commenter_id;
    }

    /**
     * Can user reply to the comment.
     *
     * @param mixed $user
     */
    public function reply($user, Comment $comment): bool {
        return $user->getKey();
    }
}
