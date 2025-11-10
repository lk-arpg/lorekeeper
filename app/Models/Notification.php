<?php

namespace App\Models;

use App\Models\User\User;

class Notification extends Model {
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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

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
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the notification message using the stored data.
     *
     * @return array
     */
    public function getMessageAttribute() {
        $notification = config('lorekeeper.notifications.'.$this->notification_type_id);

        $message = $notification['message'] ?? 'Unknown Notification';

        // Replace the URL...
        $message = isset($notification['url']) ? str_replace('{url}', url($notification['url']), $message) : $message;

        // Replace any variables in data...
        $data = $this->data;
        if ($data && count($data)) {
            foreach ($data as $key => $value) {
                $message = str_replace('{'.$key.'}', $value, $message);
            }
        }

        return $message;
    }

    /**
     * Get the notification ID from type.
     *
     * @param mixed $type
     *
     * @return array
     */
    public static function getNotificationId($type) {
        return constant('self::'.$type);
    }
    /**********************************************************************************************

        CONSTANTS

    **********************************************************************************************/

    public const CURRENCY_GRANT = 0;
    public const ITEM_GRANT = 1;
    public const CURRENCY_REMOVAL = 2;
    public const ITEM_REMOVAL = 3;
    public const CURRENCY_TRANSFER = 4;
    public const ITEM_TRANSFER = 5;
    public const FORCED_ITEM_TRANSFER = 6;
    public const CHARACTER_UPLOAD = 7;
    public const CHARACTER_CURRENCY_GRANT = 8;
    public const CHARACTER_CURRENCY_REMOVAL = 9;
    public const CHARACTER_PROFILE_EDIT = 10;
    public const IMAGE_UPLOAD = 11;
    public const CHARACTER_TRANSFER_RECEIVED = 12;
    public const CHARACTER_TRANSFER_REJECTED = 13;
    public const CHARACTER_TRANSFER_CANCELED = 14;
    public const CHARACTER_TRANSFER_DENIED = 15;
    public const CHARACTER_TRANSFER_ACCEPTED = 16;
    public const CHARACTER_TRANSFER_APPROVED = 17;
    public const CHARACTER_SENT = 18;
    public const CHARACTER_RECEIVED = 19;
    public const SUBMISSION_APPROVED = 20;
    public const SUBMISSION_REJECTED = 21;
    public const CLAIM_APPROVED = 22;
    public const CLAIM_REJECTED = 23;
    public const MYO_GRANT = 24;
    public const DESIGN_APPROVED = 25;
    public const DESIGN_REJECTED = 26;
    public const DESIGN_CANCELED = 27;
    public const TRADE_RECEIVED = 28;
    public const TRADE_UPDATE = 29;
    public const TRADE_CANCELED = 30;
    public const TRADE_COMPLETED = 31;
    public const TRADE_REJECTED = 32;
    public const TRADE_CONFIRMED = 33;
    public const BOOKMARK_TRADING = 34;
    public const BOOKMARK_GIFTS = 35;
    public const BOOKMARK_OWNER = 36;
    public const BOOKMARK_IMAGE = 37;
    public const CHARACTER_TRANSFER_ACCEPTABLE = 38;
    public const BOOKMARK_GIFT_WRITING = 39;
    public const USER_REACTIVATED = 103;
    public const USER_DEACTIVATED = 104;
    public const SUBMISSION_CANCELLED = 108;
    public const CLAIM_CANCELLED = 109;
    public const REPORT_ASSIGNED = 220;
    public const REPORT_CLOSED = 221;
    public const COMMENT_MADE = 239;
    public const COMMENT_REPLY = 240;
    public const TRADE_PROPOSAL_RECEIVED = 280;
    public const TRADE_PROPOSAL_UPDATED = 281;
    public const TRADE_PROPOSAL_ACCEPTED = 282;
    public const TRADE_PROPOSAL_REJECTED = 283;
    public const CHARACTER_ITEM_GRANT = 501;
    public const CHARACTER_ITEM_REMOVAL = 502;
    public const GALLERY_SUBMISSION_COLLABORATOR = 505;
    public const GALLERY_COLLABORATORS_APPROVED = 506;
    public const GALLERY_SUBMISSION_ACCEPTED = 507;
    public const GALLERY_SUBMISSION_REJECTED = 508;
    public const GALLERY_SUBMISSION_VALUED = 509;
    public const GALLERY_SUBMISSION_MOVED = 510;
    public const GALLERY_SUBMISSION_CHARACTER = 511;
    public const GALLERY_SUBMISSION_FAVORITE = 512;
    public const GALLERY_SUBMISSION_STAFF_COMMENTS = 513;
    public const GALLERY_SUBMISSION_EDITED = 514;
    public const GALLERY_SUBMISSION_PARTICIPANT = 515;
}
