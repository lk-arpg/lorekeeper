<?php

namespace App\Models\Gallery;

use App\Models\Model;
use App\Models\User\User;

class GalleryCollaborator extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gallery_submission_id', 'user_id',
        'has_approved', 'data', 'type',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gallery_submission_collaborators';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'user',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the submission this is attached to.
     */
    public function submission() {
        return $this->belongsTo(GallerySubmission::class, 'gallery_submission_id');
    }

    /**
     * Get the user being attached to the submission.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the display name of the participant's type.
     *
     * @return string
     */
    public function getDisplayTypeAttribute() {
        switch ($this->type) {
            default:
                flash('Invalid type selected.')->error();
                break;
            case 'Collab':
                return 'Collaborator';
                break;
            case 'Trade':
                return 'Trade With';
                break;
            case 'Gift':
                return 'Gift For';
                break;
            case 'Comm':
                return 'Commissioned';
                break;
        }
    }
}
