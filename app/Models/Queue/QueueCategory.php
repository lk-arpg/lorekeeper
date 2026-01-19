<?php

namespace App\Models\Queue;

use App\Models\Model;

class QueueCategory extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sort', 'has_image', 'description', 'parsed_description', 'hash', 'key', 'limit', 'limit_period', 'limit_concurrent', 'display',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'queue_categories';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:queue_categories|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get all this category's queues.
     */
    public function queues() {
        return $this->hasMany('App\Models\Queue\Queue', 'queue_category_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to include categories that are on display only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisplay($query) {
        return $query->where('display', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-category">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/queue-categories';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getCategoryImageFileNameAttribute() {
        return $this->id.'-'.$this->hash.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getCategoryImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getCategoryImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->categoryImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        if ($this->key) {
            return url('queues/index/'.$this->key);
        }

        return url('queues/queue-categories?name='.$this->name);
    }

    /**
     * Gets the URL for an encyclopedia search for queues in this category.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        return url('queues/queues?queue_category_id='.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/queue-categories/edit/'.$this->id);
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
     * Determine if the user has exceeded the submission limit for a category.
     *
     * @param mixed $user
     *
     * @return bool
     */
    public function checkLimit($user) {
        if (isset($this->limit)) {
            if ($this->logCount($user) >= $this->limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the count of total submissions for all queues in a category.
     *
     * @param mixed $user
     *
     * @return int
     */
    public function logCount($user) {
        if (isset($this->limit)) {
            $final = null;
            foreach ($this->queues as $q) {
                switch ($this->limit_period) {
                    case null:
                        $final = $final + QueueSubmission::submitted($q->id, $user->id)->count();
                        break;
                    case 'Hour':
                        $final = $final + QueueSubmission::submitted($q->id, $user->id)->where('created_at', '>=', now()->startOfHour())->count();
                        break;
                    case 'Day':
                        $final = $final + QueueSubmission::submitted($q->id, $user->id)->where('created_at', '>=', now()->startOfDay())->count();
                        break;
                    case 'Week':
                        $final = $final + QueueSubmission::submitted($q->id, $user->id)->where('created_at', '>=', now()->startOfWeek())->count();
                        break;
                    case 'Month':
                        $final = $final + QueueSubmission::submitted($q->id, $user->id)->where('created_at', '>=', now()->startOfMonth())->count();
                        break;
                    case 'Year':
                        $final = $final + QueueSubmission::submitted($q->id, $user->id)->where('created_at', '>=', now()->startOfYear())->count();
                        break;
                }
            }

            return $final;
        }

        return null;
    }

    /**
     * Determine if the user has exceeded the submission limit for a category.
     *
     * @param mixed $user
     *
     * @return bool
     */
    public function checkConcurrent($user) {
        if (isset($this->limit_concurrent)) {
            $final = null;
            foreach ($this->queues as $q) {
                $final = $final + QueueSubmission::pending($q->id, $user->id)->count();
            }

            if ($final >= $this->limit_concurrent) {
                return false;
            }
        }

        return true;
    }
}
