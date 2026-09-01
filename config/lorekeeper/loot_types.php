<?php

/**
 * These are the loot types that can be found in a loot table or similar, similar to getAssetKeys().
 *
 * Listing them in a config file appeared to be the most lightweight solution to storing them.
 * 'None' is implicitly handled by morphTo().
 */

return [
    'Item',
    'ItemRarity',
    'Currency',
    'LootTable',
    'ItemCategory',
    'ItemCategoryRarity',
];
