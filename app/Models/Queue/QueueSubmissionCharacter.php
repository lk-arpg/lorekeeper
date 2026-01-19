<?php

namespace App\Models\Queue;

use App\Models\Character\Character;
use App\Models\Model;
use App\Models\User\User;

class QueueSubmissionCharacter extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'queue_submission_id', 'character_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'queue_submission_characters';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the submission this is attached to.
     */
    public function submission() {
        return $this->belongsTo(QueueSubmission::class, 'queue_submission_id');
    }

    /**
     * Get the character being attached to the submission.
     */
    public function character() {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the artist of the item's image.
     *
     * @return string
     */
    public function getIconArtistAttribute() {
        if (!isset($this->data['artist_id'])) {
            return null;
        }

        $user = User::find($this->data['artist_id']);
        if ($user) {
            return $user->displayName;
        }

        return null;
    }
}
