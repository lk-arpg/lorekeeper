<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Powers
    |--------------------------------------------------------------------------
    |
    | The list of staff powers that can be attached to a user rank.
    |
    */

    'admin' => [
        'name'        => 'Administrator Access',
        'description' => 'Grants all powers automatically. Grant this power wisely.',
    ],
    'edit_site_settings' => [
        'name'        => 'Edit Site Settings',
        'description' => 'Allow rank to modify site settings and upload new images to replace the site layout images.',
    ],
    'edit_data'          => [
        'name'        => 'Edit World Data',
        'description' => 'Allow rank to modify the world data. This includes creating/editing/uploading images for species, items, traits, etc.',
    ],
    'edit_pages'         => [
        'name'        => 'Edit Text Pages',
        'description' => 'Allow rank to create/modify text pages. This includes pages created using the page creator tool and news posts.',
    ],
    'edit_user_info'     => [
        'name'        => 'Manage Users',
        'description' => 'Allow rank to view/modify user account info and create invitation keys. This will grant access to the user admin panel.',
    ],
    'edit_ranks'         => [
        'name'        => 'Edit Ranks',
        'description' => 'Allow rank to change the rank of a user. This power requires the Edit User Info power to be attached as well.',
    ],
    'edit_inventories'   => [
        'name'        => 'Edit Inventories',
        'description' => 'Allow rank to grant and remove items from user inventories, as well as grant/remove currency from users and characters.',
    ],
    'manage_characters'  => [
        'name'        => 'Manage Masterlist',
        'description' => 'Allow rank to create/edit new characters. This includes uploading new images, modifying traits on an existing character and forcing ownership transfers.',
    ],
    'manage_raffles'     => [
        'name'        => 'Manage Raffles',
        'description' => 'Allow rank to create/edit raffles, add/remove tickets for users and roll raffles.',
    ],
    'manage_submissions' => [
        'name'        => 'Manage Submissions',
        'description' => 'Allow rank to view the submissions queue, edit rewards attached to a submission and approve/reject them.',
    ],
    'manage_reports'     => [
        'name'        => 'Manage Reports',
        'description' => 'Allow rank to view the reports queue.',
    ],
    'maintenance_access' => [
        'name'        => 'Has Maintenance Access',
        'description' => 'Allow rank to browse the site normally during maintenance mode.',
    ],
    'manage_news' => [
        'name'        => 'Manage News',
        'description' => 'Allow rank to create/edit news posts.',
    ],
    'manage_sales' => [
        'name'        => 'Manage Sales',
        'description' => 'Allow rank to create/edit sales and comment on sales in preview mode.',
    ],
    'comment_on_sales' => [
        'name'        => 'Comment on Sales',
        'description' => 'Allow rank to comment on sales in preview mode.',
    ],
];
