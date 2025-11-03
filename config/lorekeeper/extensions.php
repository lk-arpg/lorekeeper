<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Extensions
    |--------------------------------------------------------------------------
    |
    | This enables/disables a selection of extensions which provide QoL and are
    | broadly applicable, but perhaps not universally, and which are contained
    | in scope enough to be readily opt-in.
    |
    | Extensions with a single value for their setting are enabled/disabled via it
    | and have no additional configuration necessary here. 0 = disabled, 1 = enabled.
    | All of the extensions here are disabled by default.
    |
    | Please refer to the readme for more information on each of these extensions.
    |
    */

    // Navbar News Notif - Juni
    'navbar_news_notif' => 0,

    // Species Trait Index - Mercury
    'species_trait_index' => 0,

    // Character Status Badges - Juni
    'character_status_badges' => 0,

    // Character TH Profile Link - Juni
    'character_TH_profile_link' => 0,

    // Design Update Voting - Mercury
    'design_update_voting' => 0,

    // Item Entry Expansion - Mercury
    'item_entry_expansion' => [
        'extra_fields' => 0,
        'resale_function' => 0,
        'loot_tables' => [
            // Adds the ability to use either rarity criteria for items or item categories with rarity criteria in loot tables. Note that disabling this does not apply retroactively.
            'enable' => 0,
            'alternate_filtering' => 0 // By default this uses more broadly compatible methods to filter by rarity. If you are on Dreamhost/know your DB software can handle searching in JSON, it's recommended to set this to 1 instead.
        ],
    ],

    // Group Traits By Category - Uri
    'traits_by_category' => 0,

    // Character Reward Expansion - Uri
    'character_reward_expansion' => [
        'expanded' => 1,
        'default_recipient' => 0, // 0 to default to the character's owner (if a user), 1 to default to the submission user.
    ],

    //queue-creator - SUPERCOOL
    'queue_creator' => [
        'expand_in_admin_index' => 0, // 1 un-nests the queues on the admin index page and lists them as individual cards with their own counts instead
        'expand_in_user_menu' => 0, // 1 un-nests the queue submissions on the user menu page and gives each one a unique page with draft/pending/approved/rejected tabs
    ]

];
