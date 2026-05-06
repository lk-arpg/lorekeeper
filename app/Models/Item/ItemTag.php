<?php

namespace App\Models\Item;

use App\Models\Model;
use Illuminate\Support\Facades\Config;

class ItemTag extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id', 'tag', 'data', 'is_active',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_tags';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the item that this tag is attached to.
     */
    public function item() {
        return $this->belongsTo(Item::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to retrieve only active tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to retrieve only a certain tag.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $tag
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, $tag) {
        return $query->where('tag', $tag);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the tag name formatted according to its colours as defined in the config file.
     *
     * @return string
     */
    public function getDisplayTagAttribute() {
        $tag = config('lorekeeper.item_tags.'.$this->tag);
        if ($tag) {
            return '<span class="badge" style="color: '.$tag['text_color'].';background-color: '.$tag['background_color'].';">'.$tag['name'].'</span>';
        }

        return null;
    }

    /**
     * Get the tag's display name.
     *
     * @return mixed
     */
    public function getName() {
        return config('lorekeeper.item_tags.'.$this->tag.'.name');
    }

    /**
     * Gets the URL of the tag's editing page.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/items/tag/'.$this->item_id.'/'.$this->tag);
    }

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Get the service associated with this tag.
     *
     * @return mixed
     */
    public function getServiceAttribute() {
        $class = 'App\Services\Item\\'.str_replace(' ', '', ucwords(str_replace('_', ' ', $this->tag))).'Service';

        return new $class;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Get the data used for editing the tag.
     *
     * @return mixed
     */
    public function getEditData() {
        return $this->service->getEditData();
    }

    /**
     * Get the data associated with the tag.
     *
     * @return mixed
     */
    public function getData() {
        return $this->service->getTagData($this);
    }
}
