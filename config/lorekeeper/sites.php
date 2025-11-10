<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sites
    |--------------------------------------------------------------------------
    |
    | This is a list of sites and appropriate regex for use in url matching,
    | for formatting links or for performing auth operations.
    | Feel free to add more sites to the "General" section, it is used solely
    | for link formatting, but be careful about making changes to any sites in the
    | "Auth" section as they are used for site functions.
    |
    */

    /**********************************************************************************************

        AUTH

    **********************************************************************************************/

    // Sites in this section use two additional keys; dA is used here as an example to demonstrate them
    // 1 = true/0 = false for both of them.
    'deviantart' => [
        'full_name'     => 'deviantART',
        'display_name'  => 'dA',
        'regex'         => '/deviantart\.com\/([A-Za-z0-9_-]+)/',
        'link'          => 'deviantart.com',
        'icon'          => 'fab fa-deviantart',

        // Auth is for whether or not the site should offer this provider as an option for users
        // to authenticate with,
        'auth'          => 1,
        // Login is for whether the site should be used as a login / registration provider instead of email verification with password
        // Login can only be used when auth has already been turned on as well.
        'login'         => 0,
        // while primary alias is whether or not an alias on this site can be a user's primary alias.
        'primary_alias' => 1,
    ],

    'toyhouse'   => [
        'full_name'     => 'Toyhou.se',
        'display_name'  => 'TH',
        'regex'         => '/toyhou\.se\/([A-Za-z0-9_-]+)/',
        'link'          => 'toyhou.se',
        'icon'          => 'fas fa-home',
        'auth'          => 1,
        'login'         => 1,
        'primary_alias' => 1,
    ],

    'twitter'    => [
        'full_name'     => 'Twitter',
        'display_name'  => 'twitter',
        'regex'         => '/twitter\.com\/([A-Za-z0-9_-]+)/',
        'link'          => 'twitter.com',
        'icon'          => 'fab fa-twitter',
        'auth'          => 0,
        'login'         => 0,
        'primary_alias' => 0,
    ],

    'instagram'  => [
        'full_name'     => 'Instagram',
        'display_name'  => 'ig',
        'regex'         => '/instagram\.com\/([A-Za-z0-9_-]+)/',
        'link'          => 'instagram.com',
        'icon'          => 'fab fa-instagram',
        'auth'          => 0,
        'login'         => 0,
        'primary_alias' => 0,
    ],

    'tumblr'     => [
        'full_name'     => 'Tumblr',
        'display_name'  => 'tumblr',
        'regex'         => '/([A-Za-z0-9_-]+)\.tumblr\.com/',
        'link'          => 'tumblr.com',
        'icon'          => 'fab fa-tumblr',
        'auth'          => 0,
        'login'         => 0,
        'primary_alias' => 0,
    ],

    'imgur'      => [
        'full_name'     => 'Imgur',
        'display_name'  => 'imgur',
        'regex'         => '/imgur\.com\/user\/([A-Za-z0-9_-]+)/',
        'link'          => 'imgur.com/user/',
        'icon'          => 'far fa-image',
        'auth'          => 0,
        'login'         => 0,
        'primary_alias' => 0,
    ],

    'twitch'     => [
        'full_name'     => 'Twitch.tv',
        'display_name'  => 'twitch',
        'regex'         => '/twitch\.tv\/([A-Za-z0-9_-]+)/',
        'link'          => 'twitch.tv',
        'icon'          => 'fab fa-twitch',
        'auth'          => 0,
        'login'         => 0,
        'primary_alias' => 0,
    ],

    'discord'    => [
        'full_name'     => 'Discord',
        'display_name'  => 'discord',
        'regex'         => '/([A-Za-z0-9_-]+)#([0-9]+)/m',
        'icon'          => 'fab fa-discord',
        'auth'          => 0,
        'primary_alias' => 0,
    ],

    /**********************************************************************************************

        GENERAL

    **********************************************************************************************/

    'artstation' => [
        'full_name'    => 'Artstation',
        'display_name' => 'artstation',
        'regex'        => '/artstation\.com\/([A-Za-z0-9_-]+)/',
        'icon'         => 'fab fa-artstation',
        'link'         => 'artstation.com',
    ],

    'picarto'    => [
        'full_name'    => 'Picarto',
        'display_name' => 'picarto',
        'regex'        => '/picarto\.tv\/([A-Za-z0-9_-]+)/',
        'icon'         => 'fas fa-brush',
        'link'         => 'picarto.tv',
    ],

    'furaffinity' => [
        'full_name'    => 'Furaffinity',
        'display_name' => 'FA',
        'regex'        => '/furaffinity\.net\/user\/([A-Za-z0-9_-]+)/',
        'icon'         => 'fas fa-paw',
        'link'         => 'furaffinity.net',
    ],
];
