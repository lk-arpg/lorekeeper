<?php

/*
|--------------------------------------------------------------------------
| Mentions
|--------------------------------------------------------------------------
|
| These are settings that affect, specifically, how mentions are used.
| Mentions are basically shorthand codes mainly for staff to refer
| to users, characters, gallery submissions and more in the WYSIWYG editor.
|
| Each mention has an 'enable' setting which decides if the mention
| functions and a 'show_text' setting which whether the "Mention This"
| block is displayed on-site. Use 0 to disable and 1 to enable.
|
| Please note that these settings are not retroactive, and mentions will
| only be applied to (or removed from) areas in which they are used when
| the text is next edited.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | User Mentions
    |--------------------------------------------------------------------------
    |
    | Links to the mentioned user, includes icon and coloration of user's rank.
    |
    | If the mentioned user changes usernames, mentions using this method break.
    |
    | Usage:
    | @username
    | - Replace username with the user's username.
    |
    | This option has been enabled by default for backwards compatibility.
    |
    */
    'user_mention'              => [
        'enable'    => 1,
        'show_text' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | User and Avatar Mentions
    |--------------------------------------------------------------------------
    |
    | Links to the mentioned user, includes user's avatar as well as the icon
    | and coloration of user's rank.
    |
    | If the mentioned user changes usernames, mentions using this method break.
    |
    | Usage:
    | %username
    | - Replace username with the user's username.
    |
    | This option has been enabled by default for backwards compatibility.
    |
    */
    'user_and_avatar_mention'   => [
        'enable'    => 1,
        'show_text' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Permalinks
    |--------------------------------------------------------------------------
    |
    | Links to the mentioned user, includes icon and coloration of user's rank.
    |
    | Mentions using this method persist even if the mentioned user changes
    | usernames, but will not update to the new username until edited again.
    |
    | Usage:
    | [user=id]
    | - Replace id with the user's id.
    |
    | This option has been enabled by default for backwards compatibility.
    |
    */
    'user_permalink'            => [
        'enable'    => 1,
        'show_text' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Avatar Permalinks
    |--------------------------------------------------------------------------
    |
    | Displays the mentioned user's avatar.
    |
    | Mentions using this method persist even if the mentioned user changes
    | usernames, but will not update to the new username until edited again.
    |
    | Usage:
    | [userav=id]
    | - Replace id with the user's id.
    |
    | This option has been enabled by default for backwards compatibility.
    |
    */
    'user_avatar_permalink'     => [
        'enable'    => 1,
        'show_text' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Character Permalinks
    |--------------------------------------------------------------------------
    |
    | Links to the mentioned character.
    |
    | Usage:
    | [character=slug]
    | - Replace slug with the character's slug.
    |
    | This option has been enabled by default for backwards compatibility.
    |
    */
    'character_permalink'       => [
        'enable'    => 1,
        'show_text' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Character Thumbnail Permalinks
    |--------------------------------------------------------------------------
    |
    | Displays the mentioned character's thumbnail.
    |
    | Usage:
    | [charthumb=slug]
    | - Replace slug with the character's slug.
    |
    | This option has been enabled by default for backwards compatibility.
    |
    */
    'character_thumb_permalink' => [
        'enable'    => 1,
        'show_text' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery Thumbnail Permalinks
    |--------------------------------------------------------------------------
    |
    | Display the mentioned gallery submission's thumbnail.
    |
    | Usage:
    | [thumb=id]
    | - Replace id with the gallery submission's id.
    |
    | This option has been enabled by default for backwards compatibility.
    |
    */
    'gallery_thumb_permalink'   => [
        'enable'    => 1,
        'show_text' => 1,
    ],

];
