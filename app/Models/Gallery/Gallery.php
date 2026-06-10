<?php

namespace App\Models\Gallery;

use App\Models\Model;
use Carbon\Carbon;

class Gallery extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'parent_id', 'name', 'sort', 'description',
        'currency_enabled', 'votes_required', 'submissions_open',
        'start_at', 'end_at', 'hide_before_start', 'prompt_selection',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'galleries';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    /**
     * Validation rules for character creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:galleries|between:3,50',
        'description' => 'nullable',
    ];

    /**
     * Validation rules for character updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|between:3,50',
        'description' => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the parent gallery.
     */
    public function parent() {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child galleries of this gallery.
     */
    public function children() {
        return $this->hasMany(self::class, 'parent_id')->sort();
    }

    /**
     * Get the sibling galleries of this gallery.
     */
    public function siblings() {
        if ($this->parent) {
            return $this->parent->hasMany(self::class, 'parent_id')->sort();
        }

        return null;
    }

    /**
     * Get the avunculi galleries of this gallery.
     */
    public function avunculi() {
        if ($this->parent && $this->parent->siblings()) {
            return $this->parent->siblings()->sort();
        }

        return null;
    }

    /**
     * Get the submissions made to this gallery.
     */
    public function submissions() {
        return $this->hasMany(GallerySubmission::class, 'gallery_id')->visible()->orderBy('created_at', 'DESC');
    }

    /**
     * Get the criteria attached to this gallery.
     */
    public function criteria() {
        return $this->hasMany(GalleryCriterion::class, 'gallery_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to return galleries sorted first by sort number and then name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSort($query) {
        return $query->orderByRaw('ISNULL(sort), sort ASC')->orderBy('name', 'ASC');
    }

    /**
     * Scope a query to only include active galleries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query
            ->where(function ($query) {
                $query->whereNull('start_at')->orWhere('start_at', '<', Carbon::now())->orWhere(function ($query) {
                    $query->where('start_at', '>=', Carbon::now())->where('hide_before_start', 0);
                });
            })->where(function ($query) {
                $query->whereNull('end_at')->orWhere('end_at', '>', Carbon::now())->orWhere(function ($query) {
                    $query->where('end_at', '<=', Carbon::now());
                });
            });
    }

    /**
     * Scope a query to only include visible galleries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query) {
        return $query
            ->where(function ($query) {
                $query->whereNull('start_at')->orWhere('start_at', '<', Carbon::now())->orWhere(function ($query) {
                    $query->where('start_at', '>=', Carbon::now())->where('hide_before_start', 0);
                });
            });
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the gallery's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-prompt">'.$this->name.'</a>';
    }

    /**
     * Gets the gallery's URL.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('gallery/'.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/galleries/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**
     * Gets whether or not the user can submit to the gallery.
     *
     * @param bool       $submissionsOpen
     * @param mixed|null $user
     *
     * @return string
     */
    public function canSubmit($submissionsOpen, $user = null) {
        if ($submissionsOpen) {
            if ((isset($this->start_at) && $this->start_at->isFuture()) || (isset($this->end_at) && $this->end_at->isPast())) {
                return false;
            } elseif ($user && $user->hasPower('manage_submissions')) {
                return true;
            } elseif ($this->submissions_open) {
                return true;
            }
        } else {
            return false;
        }
    }
}
