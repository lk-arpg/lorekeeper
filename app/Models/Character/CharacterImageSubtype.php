<?php

namespace App\Models\Character;

use App\Models\Model;
use App\Models\Species\Subtype;

class CharacterImageSubtype extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_image_id', 'subtype_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_image_subtypes';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the image associated with this record.
     */
    public function image() {
        return $this->belongsTo('App\Models\Character\CharacterImage', 'character_image_id');
    }

    /**
     * Get the subtype associated with this record.
     */
    public function subtype() {
        return $this->belongsTo(Subtype::class, 'subtype_id');
    }
}
