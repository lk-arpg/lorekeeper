<?php

namespace App\Models\User;

use App\Models\Model;

class UserSettings extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'is_fto', 'submission_count', 'banned_at', 'ban_reason', 'birthday_setting',
        'deactivate_reason', 'deactivated_at', 'content_warning_visibility', 'allow_profile_comments',
        'css_fonts_disabled', 'font_size', 'letter_spacing', 'word_spacing', 'line_height',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_settings';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'banned_at'      => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    /**
     * The primary key of the model.
     *
     * @var string
     */
    public $primaryKey = 'user_id';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this set of settings belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
