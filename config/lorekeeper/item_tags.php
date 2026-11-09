<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Item tags
    |--------------------------------------------------------------------------
    |
    | This is a list of tags that can be attached to items.
    | Add tags here to make them selectable in the admin panel.
    | The key must be unique, but names do not have to be.
    |
    */

    'box'  => [
        'name'             => 'Box',
        'text_color'       => '#ffffff',
        'background_color' => '#f6993f',
        'description'      => 'This item can be opened for a preset reward.',
    ],

    'slot' => [
        'name'             => 'Slot',
        'text_color'       => '#ffffff',
        'background_color' => '#1fd1a7',
        'description'      => 'This item can be used to create an MYO slot.',
    ],

    'coupon' => [
        'name'             => 'Coupon',
        'text_color'       => '#ffffff',
        'background_color' => '#ff5ca8',
        'description'      => 'This item can be redeemed at an eligible shop for a discount.',
    ],
];
