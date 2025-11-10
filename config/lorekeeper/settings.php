<?php

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
|
| These are settings that affect how the site works.
| These are not expected to be changed often or on short schedule and are
| therefore separate from the settings modifiable in the admin panel.
| It's highly recommended that you do any required modifications to this file
| as well as config/app.php before you start using the site.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | This is the current version of Lorekeeper that your site is on.
    | Do not change this value!
    |
    */
    'version'                                           => '3.0.0',

    /*
    |--------------------------------------------------------------------------
    | Site Name
    |--------------------------------------------------------------------------
    |
    | This differs from the app name in that it is allowed to contain spaces
    | (APP_NAME in .env cannot take spaces). This will be displayed on the
    | site wherever the name needs to be displayed.
    |
    */
    'site_name'                                         => 'Lorekeeper',

    /*
    |--------------------------------------------------------------------------
    | Site Description
    |--------------------------------------------------------------------------
    |
    | This is the description used for the site in meta tags-- previews
    | displayed on various social media sites, discord, and the like.
    | It is not, however, displayed on the site itself. This should be kept short and snappy!
    |
    */
    'site_desc'                                         => 'A Lorekeeper ARPG',

    /*
    |--------------------------------------------------------------------------
    | Alias | Email Requirement
    |--------------------------------------------------------------------------
    |
    | Whether or not users are required to link an off-site account to access
    | the site's full features. Note that this does not disable aliases outright,
    | and you should still set up at least one of the auth options provided.
    | Note also that any functionality which makes use of the alias system
    | (e.g. ownership checking for characters only associated with an off-site account)
    | will still work provided users link the relevant alias(es).
    |
    | The email option functions as a fallback for users who register with an off-site provider.
    | If they do not have an email associated with their off-site account, they will be prompted to
    | provide one on registration / login / site interaction (if this setting is enabled).
    |
    */
    'require_alias'                                     => 1,
    'require_email'                                     => 1,

    /*
    |--------------------------------------------------------------------------
    | Character Codes
    |--------------------------------------------------------------------------
    |
    | character_codes:
    |       This is used in the automatic generation of character codes.
    |       {category}: This is replaced by the character category code.
    |       {number}: This is replaced by the character number.
    |       {year}: This is replaced by the current year.
    |
    |       e.g. Under the default setting ({category}-{number}),
    |       a character in a category called "MYO" (code "MYO") with number 001
    |       will have the character code of MYO-001.
    |
    |       !IMPORTANT!
    |       As this is used to generate the character's URL, sticking to
    |       alphanumeric, hyphen (-) and underscore (_) characters
    |       is advised.
    |
    | character_number_digits:
    |       This specifies the default number of digits for {number} when
    |       pulled automatically.
    |
    |       e.g. If the next number is 2, setting this to 3 would give 002.
    |
    | character_pull_number:
    |       This determines if the next {number} is pulled from the highest
    |       existing number, or the highest number in the category.
    |       This value can be "all" (default) or "category".
    |
    |       e.g. if the following characters exist:
    |       Standard (STD) category: STD-001, STD-002, STD-003
    |       MYO (MYO) category:      MYO-001, MYO-002
    |       If character_pull_number is 'all':
    |           The next number pulled will be 004 regardless of category.
    |       If character_pull_number is 'category':
    |           The next number pulled for STD will be 004.
    |           The next number pulled for MYO will be 003.
    |
    | reset_character_status_on_transfer:
    |       This determines whether owner-set character status--
    |       trading, gift art, and gift writing--
    |       should be cleared when the character is transferred to a new owner.
    |       Default: 0/Disabled, 1 to enable.
    |
    | reset_character_profile_on_transfer:
    |       This determines whether character name and profile should be cleared
    |       when the character is transferred to a new owner.
    |       Default: 0/Disabled, 1 to enable.
    |
    | clear_myo_slot_name_on_approval:
    |       Whether the "name" given to a MYO slot should be cleared when a design update for it is approved/
    |       the slot becomes a full character.
    |       Default: 0/Disabled, 1 to enable.
    |
    */
    'character_codes'                                   => '{category}-{number}',
    'character_number_digits'                           => 3,
    'character_pull_number'                             => 'all',

    'reset_character_status_on_transfer'                => 0,
    'reset_character_profile_on_transfer'               => 0,
    'clear_myo_slot_name_on_approval'                   => 0,

    /*
    |--------------------------------------------------------------------------
    | Masterlist Images
    |--------------------------------------------------------------------------
    |
    | 0: Do not watermark. 1: Automatically watermark masterlist images.
    |
    | Dimension, in pixels, to scale submitted masterlist images to. Enter "0" to disable resizing.
    |
    | Which dimension to scale submitted masterlist images on. Options are 'shorter' and 'longer'.
    | Only takes effect if masterlist_image_dimension is set. Defaults to 'shorter'.
    |
    | File format to encode masterlist image uploads to.
    | Set to null to leave images in their original formats.
    | Example:
    | 'masterlist_image_format' => null,
    |
    | Color to fill non-transparent images in when masterlist_image_format is set.
    | This is in an endeavor to make images with a transparent background
    | compress better. Set to null to disable.
    | Example:
    | 'masterlist_image_background' => '#ffffff',
    |
    */
    'watermark_masterlist_images'                       => 0,

    'masterlist_image_dimension'                        => 0,
    'masterlist_image_dimension_target'                 => 'shorter',

    'masterlist_image_format'                           => null,
    'masterlist_image_background'                       => '#ffffff',

    /*
    |--------------------------------------------------------------------------
    | Masterlist Image Fullsizes
    |--------------------------------------------------------------------------
    |
    | 0: Do not store full-sized masterlist images (for view by the character\'s owner) and staff.
    | 1: Store full-sized images uploaded to the masterlist. Not retroactive either way.
    |
    | Size, in pixels, to cap full-sized masterlist images at (if storing full-sized images is enabled).
    | Images above this cap in either dimension will be resized to suit. Enter "0" to disable resizing.
    |
    | File format to encode full-sized masterlist image uploads to.
    | Set to null to leave images in their original formats.
    | Example:
    | 'masterlist_fullsizes_format' => null,
    |
    */
    'store_masterlist_fullsizes'                        => 0,
    'masterlist_fullsizes_cap'                          => 0,
    'masterlist_fullsizes_format'                       => null,

    /*
    |--------------------------------------------------------------------------
    | Masterlist Thumbnail Dimensions & Watermarking
    |--------------------------------------------------------------------------
    |
    | This affects the dimensions used by the character thumbnail cropper.
    | Using a smallish size is recommended to reduce the amount of time
    | needed to load the masterlist pages.
    |
    | 0: Default thumbnail cropping behavior. 1: Watermark thumbnails.
    | Expects the whole of the character to be visible in the thumbnail.
    |
    */
    'masterlist_thumbnails'                             => [
        'width'  => 200,
        'height' => 200,
    ],

    'watermark_masterlist_thumbnails'                   => 0,

    /*
    |--------------------------------------------------------------------------
    | Watermark Resizing
    |--------------------------------------------------------------------------
    |
    | This affects the size of the watermark, resizing it to fit the masterlist image.
    | This requires the 'watermark_masterlist_images' option to be set to 1.
    |
    | 0: Does not automatically resize watermark. 1: Resize watermarks.
    | Expects the whole of the character to be visible in the thumbnail.
    |
    | The watermark percent is the scale of the watermark.
    | The default is '0.9', or 90 percent of the image to be watermarked.
    |
    | The final option is to also resize watermarks on thumbnails.
    | It will assume the same scale as masterlist image.
    | 0: Does not resize thumbnail watermarks. 1: Resizes thumbnail watermarks.
    | This requires the 'watermark_masterlist_thumbnails' option to be set to 1.
    |
    */

    'watermark_resizing'                                => 0,
    'watermark_percent'                                 => 0.9,
    'watermark_resizing_thumb'                          => 0,

    /*
    |--------------------------------------------------------------------------
    | Masterlist Image Automation Replacing Cropper
    |--------------------------------------------------------------------------
    |
    | This feature will replace the thumbnail cropper as option at image uploads.
    | It will automatically add transparent borders to the images to make them square,
    | based on the bigger dimension (between width/height).
    | Thumbnails will effectively be small previews of the full masterlist images.
    | This feature does not disable the manual uploading of thumbnail images.
    |
    | Simply change to "1" to enable, or keep at "0" to disable.
    |
    */
    'masterlist_image_automation'                       => 0,

    /*
    |--------------------------------------------------------------------------
    | Masterlist Image Automation Hide Manual Thumbnail
    |--------------------------------------------------------------------------
    |
    | NOTE: If the "Masterlist Image Automation Replacing Cropper"
    | setting above is disabled, this setting has no effect.
    |
    | This disables the option for users to manually upload their own
    | thumbnail images in design updates, including use of the cropper.
    | Note that this does not prevent permissioned staff from uploading
    | custom thumbnail images.
    |
    | 0: Allows custom thumbnail uploads.
    | 1: Disallows custom thumbnail uploads.
    |
    */
    'masterlist_image_automation_hide_manual_thumbnail' => 0,

    /*
    |--------------------------------------------------------------------------
    | Remove Manual Thumbnail Image Upload
    |--------------------------------------------------------------------------
    |
    | NOTE: If the "Masterlist Image Automation Hide Manual Thumbnail"
    | setting above is enabled, this setting has no effect.
    |
    | This disables the option for users to manually upload their own
    | thumbnail images in design updates, requiring use of the cropper.
    | Note that this does not prevent permissioned staff from uploading
    | custom thumbnail images.
    |
    | 0: Allows custom thumbnail uploads.
    | 1: Disallows custom thumbnail uploads.
    |
    */
    'hide_manual_thumbnail_image_upload' => 0,

    /*
    |--------------------------------------------------------------------------
    | Gallery Image Settings
    |--------------------------------------------------------------------------
    |
    | This affects images submitted to on-site galleries.
    |
    | Size, in pixels, to cap gallery images at.
    | Images above this cap in either dimension will be resized to suit. Enter "0" to disable resizing.
    |
    | File format to encode gallery image uploads to.
    | Set to null to leave images in their original formats.
    | Example:
    | 'gallery_images_format' => null,
    |
    */
    'gallery_images_cap'    => 0,
    'gallery_images_format' => null,

    /*
    |--------------------------------------------------------------------------
    | Trade Asset Limit
    |--------------------------------------------------------------------------
    |
    | This is an arbitrary upper limit on how many things (items, currencies,
    | characters) a trade can contain. While this can potentially be higher,
    | there are limits on data storage, so raising this is not recommended.
    |
    */
    'trade_asset_limit'                                 => 20,

    /*
    |--------------------------------------------------------------------------
    | Shop Purchase Limit
    |--------------------------------------------------------------------------
    |
    | This is an arbitrary upper limit on how many items a user can buy in a
    | single shop transaction.
    |
    */
    'default_purchase_limit'                            => 99,

    /*
    |--------------------------------------------------------------------------
    | Currency Symbol
    |--------------------------------------------------------------------------
    |
    | Symbol for the (real world) currency used for sales posts.
    |
    */
    'currency_symbol'                                   => '$',

    /*
    |--------------------------------------------------------------------------
    | User Username Changes
    |--------------------------------------------------------------------------
    |
    | allow_username_changes: Whether or not users can change their usernames.
    | Set to 0 to disable.
    |
    | username_change_cooldown: Cooldown period, in days, before a user can change their username again.
    | Set to 0 / null to disable.
    |
    */

    'allow_username_changes'                            => 0,
    'username_change_cooldown'                          => 30,

    /*
    |--------------------------------------------------------------------------
    | What You See Is What You Get (WYSIWYG) Comments
    |--------------------------------------------------------------------------
    |
    | Whether or not to use a WYSIWYG editor for comments.
    | 1: Use WYSIWYG editor. 0: Use markdown / plain text editor.
    |
    */
    'wysiwyg_comments'                                  => 1,

    /*
    |--------------------------------------------------------------------------
    | Allow Gallery Submissions on Prompts
    |--------------------------------------------------------------------------
    |
    | Whether or not to allow gallery submissions on prompts.
    |
    */
    'allow_gallery_submissions_on_prompts'              => 1,

    /*
    |--------------------------------------------------------------------------
    | Hideable Textarea on Gallery Submissions
    |--------------------------------------------------------------------------
    |
    | Whether or not to be able to hide the textarea on gallery Submissions.
    |
    | enable: Set to 1 to show a button to hide the textarea.
    |
    | on_image Set to 1 to auto-hide on image upload- will only work
    | if 'enable' is set to 1.
    |
    */
    'hide_textarea_on_gallery_submissions'              => [
        'enable'   => 0,
        'on_image' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Site FontAwesome Icon Version
    |--------------------------------------------------------------------------
    |
    | What version of FontAwesome the site uses.
    | 0: Version 5. (Default) 1: Version 6.
    | 2: A mixed version where icons with v5 classes (i.e. fas) show
    | the v5 icons and icons with v6 classes (i.e. fa-solid) show the v6 icons.
    |
    */
    'fa_version'                                  => 0,

    /*
    |--------------------------------------------------------------------------
    | Site Logging Webhook
    |--------------------------------------------------------------------------
    |
    | This is the webhook URL for site actions logging.
    | This is used to send a webhook to the site administrators alerting them
    | of any actions that may be considered suspicious or harmful.
    | This is intended to be a Discord webhook, but can be used with other services with minor modifications.
    |
    */
    'site_logging_webhook'                              => env('SITE_LOGGING_WEBHOOK', null),

    /*
    |--------------------------------------------------------------------------
    | Enable Character Content Warnings
    |--------------------------------------------------------------------------
    |
    | Allows characters to have content warnings.
    |
    */
    'enable_character_content_warnings'                 => 1,
];
