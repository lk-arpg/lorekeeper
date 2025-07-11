<?php

namespace App\Models\Submission;

use App\Models\Character\Character;
use App\Models\Model;

class SubmissionCharacter extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'submission_id', 'character_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'submission_characters';

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
        return $this->belongsTo(Submission::class, 'submission_id');
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
     * Get the rewards for the character.
     *
     * @return array
     */
    public function getRewardsAttribute() {
        $assets = parseAssetData($this->data);
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
