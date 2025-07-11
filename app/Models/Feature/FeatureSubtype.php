<?php

namespace App\Models\Feature;

use App\Models\Model;
use App\Models\Species\Subtype;

class FeatureSubtype extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'feature_id', 'subtype_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feature_subtypes';

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
     * Get the feature associated with this record.
     */
    public function feature() {
        return $this->belongsTo(Feature::class, 'feature_id');
    }

    /**
     * Get the subtype associated with this record.
     */
    public function subtype() {
        return $this->belongsTo(Subtype::class, 'subtype_id');
    }
}
