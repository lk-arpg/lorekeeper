<?php

namespace App\Models;

use App\Models\User\User;
use App\Traits\Commentable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class News extends Model implements Feedable {
    use Commentable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'text', 'parsed_text', 'title', 'is_visible', 'post_at',
        'has_image', 'hash',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'post_at' => 'datetime',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'title' => 'required|between:3,100',
        'text'  => 'required',
        'image' => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'title' => 'required|between:3,100',
        'text'  => 'required',
        'image' => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who created the news post.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('manage_news')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to only include posts that are scheduled to be posted and are ready to post.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShouldBeVisible($query) {
        return $query->whereNotNull('post_at')->where('post_at', '<', Carbon::now())->where('is_visible', 0);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the news slug.
     *
     * @return bool
     */
    public function getSlugAttribute() {
        return $this->id.'.'.Str::slug($this->title);
    }

    /**
     * Displays the news post title, linked to the news post itself.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'">'.$this->title.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/news';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-'.$this->hash.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the news post URL.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('news/'.$this->slug);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/news/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'manage_news';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Returns all feed items.
     */
    public static function getFeedItems() {
        return self::visible()->get();
    }

    /**
     * Generates feed item information.
     *
     * @return /Spatie/Feed/FeedItem;
     */
    public function toFeedItem(): FeedItem {
        return FeedItem::create([
            'id'         => '/news/'.$this->id,
            'title'      => $this->title,
            'summary'    => $this->parsed_text,
            'updated'    => $this->updated_at,
            'link'       => $this->url,
            'author'     => $this->user->name,
            'authorName' => $this->user->name,
        ]);
    }
}
