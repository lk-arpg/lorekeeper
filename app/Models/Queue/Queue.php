<?php
namespace App\Models\Queue;

use App\Models\Item\Item;
use App\Models\Model;
use App\Services\Queue\GeneralService;
use Carbon\Carbon;

class Queue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'queue_category_id', 'name', 'summary', 'description', 'parsed_description', 'is_active',
        'start_at', 'end_at', 'hide_before_start', 'hide_after_end', 'has_image', 'prefix',
        'hide_submissions', 'staff_only', 'hash', 'form', 'parsed_form', 'queue_type', 'data', 'checklist', 'limit', 'limit_period', 'output', 'limit_concurrent',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'queues';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_at'  => 'datetime',
        'end_at'    => 'datetime',
        'data'      => 'array',
        'checklist' => 'array',
        'output'    => 'array',
    ];

    /**
     * Validation rules for character creation.
     *
     * @var array
     */
    public static $createRules = [
        'queue_category_id' => 'nullable',
        'name'              => 'required|unique:queues|between:3,100',
        'prefix'            => 'nullable|unique:queues|between:2,10',
        'summary'           => 'nullable',
        'description'       => 'nullable',
        'image'             => 'mimes:png',
    ];

    /**
     * Validation rules for character updating.
     *
     * @var array
     */
    public static $updateRules = [
        'queue_category_id' => 'nullable',
        'name'              => 'required|between:3,100',
        'prefix'            => 'nullable|between:2,10',
        'summary'           => 'nullable',
        'description'       => 'nullable',
        'image'             => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category the queue belongs to.
     */
    public function category()
    {
        return $this->belongsTo(QueueCategory::class, 'queue_category_id');
    }

    /**
     * Get the submissions that belong to this queue.
     */
    public function submissions()
    {
        return $this->hasMany(QueueSubmission::class, 'queue_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active queues.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)
            ->where(function ($query) {
                $query->whereNull('start_at')->orWhere('start_at', '<', Carbon::now())->orWhere(function ($query) {
                    $query->where('start_at', '>=', Carbon::now())->where('hide_before_start', 0);
                });
            })->where(function ($query) {
            $query->whereNull('end_at')->orWhere('end_at', '>', Carbon::now())->orWhere(function ($query) {
                $query->where('end_at', '<=', Carbon::now())->where('hide_after_end', 0);
            });
        });
    }

    /**
     * Scope a query to open or closed queues.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $isOpen
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query, $isOpen)
    {
        if ($isOpen) {
            $query->where(function ($query) {
                $query->whereNull('end_at')->where('start_at', '<', Carbon::now());
            })->orWhere(function ($query) {
                $query->whereNull('start_at')->where('end_at', '>', Carbon::now());
            })->orWhere(function ($query) {
                $query->where('start_at', '<', Carbon::now())->where('end_at', '>', Carbon::now());
            })->orWhere(function ($query) {
                $query->whereNull('end_at')->whereNull('start_at');
            });
        } else {
            $query->where(function ($query) {
                $query->whereNull('end_at')->where('start_at', '>', Carbon::now());
            })->orWhere(function ($query) {
                $query->whereNull('start_at')->where('end_at', '<', Carbon::now());
            })->orWhere('start_at', '>', Carbon::now())->orWhere('end_at', '<', Carbon::now());
        }
    }

    /**
     * Scope a query to include or exclude staff-only queues.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User\User                 $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStaffOnly($query, $user)
    {
        if ($user && $user->isStaff) {
            return $query;
        }

        return $query->where('staff_only', 0);
    }

    /**
     * Scope a query to sort queues in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false)
    {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort queues in category order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query)
    {
        if (QueueCategory::all()->count()) {
            return $query->orderBy(QueueCategory::select('sort')->whereColumn('queues.queue_category_id', 'queue_categories.id'), 'DESC');
        }

        return $query;
    }

    /**
     * Scope a query to sort features by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to sort queues by start date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortStart($query, $reverse = false)
    {
        return $query->orderBy('start_at', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort queues by end date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortEnd($query, $reverse = false)
    {
        return $query->orderBy('end_at', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort queues by end date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSplash($query)
    {
        $query->whereHas('category', function ($query) {
            $query->where('key', null);
        })->orWhere('queue_category_id', null);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="' . $this->url . '" class="display-queue">' . $this->name . '</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/queues';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute()
    {
        return $this->id . '-' . $this->hash . '-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (! $this->has_image) {
            return null;
        }

        return asset($this->imageDirectory . '/' . $this->imageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('queues/queues?name=' . $this->name);
    }

    /**
     * Gets the URL of the individual queue's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute()
    {
        return url('queues/' . $this->id);
    }

    /**
     * Gets the queue's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'queues';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute()
    {
        return url('admin/data/queues/edit/' . $this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute()
    {
        return 'edit_data';
    }

    /**
     * Get the service associated with the associated type.
     *
     * @return mixed
     */
    public function getServiceAttribute()
    {
        $class = 'App\Services\Queue\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->queue_type))) . 'Service';
        return (new $class());
    }

    /**
     * Get the config data
     *
     * @return mixed
     */
    public function getConfigInfoAttribute()
    {
        return config('lorekeeper.queue_types.' . $this->queue_type);
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getCustomImageDirectoryAttribute()
    {
        return 'images/data/queues/images';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function customImageFileName($key)
    {
        return $this->id . '-' . $key . '.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getCustomImagePathAttribute()
    {
        return public_path($this->customImageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function customImageUrl($key)
    {
        return asset($this->customImageDirectory . '/' . $this->CustomImageFileName($key));
    }

    /**
     * Check that custom image exists
     *
     * @return string
     */
    public function customImageExists($key)
    {
        return file_exists($this->customImagePath . '/' . $this->CustomImageFileName($key));
    }

    /**
     * Get the general service
     *
     * @return mixed
     */
    public function getGeneralServiceAttribute()
    {
        return (new GeneralService());
    }

    /**
     * Get the config data
     *
     * @return mixed
     */
    public function configSet($key)
    {
        if (isset($this->configInfo[$key]) && $this->configInfo[$key] == true) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves any data that should be used in the holiday type on the user side
     *
     */
    public function getItemsAttribute()
    {
        if (! isset($this->data['items'])) {
            return [];
        }

        $final = [];
        foreach ($this->data['items'] as $item) {
            $final[] = Item::find($item);
        }

        return $final;
    }

    /**********************************************************************************************
    OTHER
     **********************************************************************************************/

    public function checkLimit($user)
    {
        //categories supersede all.
        if ($this->queue_category_id && isset($this->category->limit)) {
            return $this->category->checkLimit($user);
        }

        if (isset($this->limit)) {
            if ($this->logCount($user) >= $this->limit) {
                return false;
            }

        }
        return true;
    }

    public function logCount($user)
    {
        if (isset($this->limit)) {

            switch ($this->limit_period) {
                case null:
                    return QueueSubmission::submitted($this->id, $user->id)->count();
                    break;
                case 'Hour':
                    return QueueSubmission::submitted($this->id, $user->id)->where('created_at', '>=', now()->startOfHour())->count();
                    break;
                case 'Day':
                    return QueueSubmission::submitted($this->id, $user->id)->where('created_at', '>=', now()->startOfDay())->count();
                    break;
                case 'Week':
                    return QueueSubmission::submitted($this->id, $user->id)->where('created_at', '>=', now()->startOfWeek())->count();
                    break;
                case 'Month':
                    return QueueSubmission::submitted($this->id, $user->id)->where('created_at', '>=', now()->startOfMonth())->count();
                    break;
                case 'Year':
                    return QueueSubmission::submitted($this->id, $user->id)->where('created_at', '>=', now()->startOfYear())->count();
                    break;
            }

        }
        return null;
    }

    public function checkConcurrent($user)
    {
        //categories supersede all.
        if ($this->queue_category_id && isset($this->category->limit_concurrent)) {
            return $this->category->checkConcurrent($user);
        }

        if (isset($this->limit_concurrent)) {
            if (QueueSubmission::pending($this->id, $user->id)->count() >= $this->limit_concurrent) {
                return false;
            }

        }
        return true;
    }

    /**
     * Gets the decoded output json
     *
     * @return array
     */
    public function getRewardsAttribute()
    {
        $rewards = [];
        if (isset($this->output['users'])) {
            $assets = $this->getRewardItemsAttribute();

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
        }
        return $rewards;
    }

    /**
     * Interprets the json output and retrieves the corresponding items
     *
     * @return array
     */
    public function getRewardItemsAttribute()
    {
        return parseAssetData($this->output['users']);
    }

    /**
     * Gets the decoded output json
     *
     * @return array
     */
    public function getCharacterRewardsAttribute()
    {
        $rewards = [];
        if (isset($this->output['characters'])) {
            $assets = $this->getCharacterRewardItemsAttribute();

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
        }
        return $rewards;
    }

    /**
     * Interprets the json output and retrieves the corresponding items
     *
     * @return array
     */
    public function getCharacterRewardItemsAttribute()
    {
        return parseAssetData($this->output['characters']);
    }

}
