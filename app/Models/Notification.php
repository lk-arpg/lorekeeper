<?php
namespace App\Models;

use App\Models\Model;
use Config;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'notification_type_id', 'is_unread', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns notification.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Get the notification message using the stored data.
     *
     * @return array
     */
    public function getMessageAttribute()
    {
        $notification = Config::get('lorekeeper.notifications.' . $this->notification_type_id);

        $message = $notification['message'];

        // Replace the URL...
        $message = str_replace('{url}', url($notification['url']), $message);

        // Replace any variables in data...
        $data = $this->data;
        if ($data && count($data)) {
            foreach ($data as $key => $value) {
                $message = str_replace('{' . $key . '}', $value, $message);
            }
        }

        return $message;
    }

    /**
     * Get the notification ID from type.
     *
     * @return array
     */
    public static function getNotificationId($type)
    {
        return constant('self::' . $type);
    }

    /**********************************************************************************************

        CONSTANTS

    **********************************************************************************************/

    const CURRENCY_GRANT                    = 0;
    const ITEM_GRANT                        = 1;
    const CURRENCY_REMOVAL                  = 2;
    const ITEM_REMOVAL                      = 3;
    const CURRENCY_TRANSFER                 = 4;
    const ITEM_TRANSFER                     = 5;
    const FORCED_ITEM_TRANSFER              = 6;
    const CHARACTER_UPLOAD                  = 7;
    const CHARACTER_CURRENCY_GRANT          = 8;
    const CHARACTER_CURRENCY_REMOVAL        = 9;
    const CHARACTER_PROFILE_EDIT            = 10;
    const IMAGE_UPLOAD                      = 11;
    const CHARACTER_TRANSFER_RECEIVED       = 12;
    const CHARACTER_TRANSFER_REJECTED       = 13;
    const CHARACTER_TRANSFER_CANCELED       = 14;
    const CHARACTER_TRANSFER_DENIED         = 15;
    const CHARACTER_TRANSFER_ACCEPTED       = 16;
    const CHARACTER_TRANSFER_APPROVED       = 17;
    const CHARACTER_SENT                    = 18;
    const CHARACTER_RECEIVED                = 19;
    const SUBMISSION_APPROVED               = 20;
    const SUBMISSION_REJECTED               = 21;
    const CLAIM_APPROVED                    = 22;
    const CLAIM_REJECTED                    = 23;
    const MYO_GRANT                         = 24;
    const DESIGN_APPROVED                   = 25;
    const DESIGN_REJECTED                   = 26;
    const DESIGN_CANCELED                   = 27;
    const TRADE_RECEIVED                    = 28;
    const TRADE_UPDATE                      = 29;
    const TRADE_CANCELED                    = 30;
    const TRADE_COMPLETED                   = 31;
    const TRADE_REJECTED                    = 32;
    const TRADE_CONFIRMED                   = 33;
    const BOOKMARK_TRADING                  = 34;
    const BOOKMARK_GIFTS                    = 35;
    const BOOKMARK_OWNER                    = 36;
    const BOOKMARK_IMAGE                    = 37;
    const CHARACTER_TRANSFER_ACCEPTABLE     = 38;
    const BOOKMARK_GIFT_WRITING             = 39;
    const REPORT_ASSIGNED                   = 220;
    const REPORT_CLOSED                     = 221;
    const COMMENT_MADE                      = 239;
    const COMMENT_REPLY                     = 240;
    const CHARACTER_ITEM_GRANT              = 501;
    const CHARACTER_ITEM_REMOVAL            = 502;
    const GALLERY_SUBMISSION_COLLABORATOR   = 505;
    const GALLERY_COLLABORATORS_APPROVED    = 506;
    const GALLERY_SUBMISSION_ACCEPTED       = 507;
    const GALLERY_SUBMISSION_REJECTED       = 508;
    const GALLERY_SUBMISSION_VALUED         = 509;
    const GALLERY_SUBMISSION_MOVED          = 510;
    const GALLERY_SUBMISSION_CHARACTER      = 511;
    const GALLERY_SUBMISSION_FAVORITE       = 512;
    const GALLERY_SUBMISSION_STAFF_COMMENTS = 513;
    const GALLERY_SUBMISSION_EDITED         = 514;
    const GALLERY_SUBMISSION_PARTICIPANT    = 515;
    const QUEUE_SUBMISSION_APPROVED         = 1116;
    const QUEUE_SUBMISSION_REJECTED         = 1117;
    const QUEUE_SUBMISSION_CANCELLED        = 1118;
}
