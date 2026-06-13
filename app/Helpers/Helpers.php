<?php

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| Miscellaneous helper functions, primarily used for formatting.
|
*/

/**
 * Returns class name if the current URL corresponds to the given path.
 *
 * @param string $path
 * @param string $class
 *
 * @return string
 */
function set_active($path, $class = 'active') {
    return call_user_func_array('Request::is', (array) $path) ? $class : '';
}

/**
 * Adds a help icon with a tooltip.
 *
 * @param string $text
 *
 * @return string
 */
function add_help($text) {
    return '<i class="fas fa-question-circle help-icon" data-toggle="tooltip" title="'.$text.'"></i>';
}

/**
 * Uses the given array to generate breadcrumb links.
 *
 * @param array $links
 *
 * @return string
 */
function breadcrumbs($links) {
    $ret = '<nav><ol class="breadcrumb">';
    $count = 0;
    $ret .= '<li class="breadcrumb-item"><a href="'.url('/').'">'.config('lorekeeper.settings.site_name', 'Lorekeeper').'</a></li>';
    foreach ($links as $key => $link) {
        $isLast = ($count == count($links) - 1);

        $ret .= '<li class="breadcrumb-item ';
        if ($isLast) {
            $ret .= 'active';
        }
        $ret .= '">';

        if (!$isLast) {
            $ret .= '<a href="'.url($link).'">';
        }
        $ret .= $key;
        if (!$isLast) {
            $ret .= '</a>';
        }

        $ret .= '</li>';

        $count++;
    }
    $ret .= '</ol></nav>';

    return $ret;
}

/**
 * Formats the timestamp to a standard format.
 *
 * @param Illuminate\Support\Carbon\Carbon $timestamp
 * @param mixed                            $showTime
 *
 * @return string
 */
function format_date($timestamp, $showTime = true) {
    return $timestamp->format('j F Y'.($showTime ? ', H:i:s' : '')).($showTime ? ' <abbr data-toggle="tooltip" title="UTC'.$timestamp->timezone->toOffsetName().'">'.strtoupper($timestamp->timezone->getAbbreviatedName($timestamp->isDST())).'</abbr>' : '');
}

function pretty_date($timestamp, $showTime = true) {
    return '<abbr data-toggle="tooltip" title="'.$timestamp->format('F j Y'.($showTime ? ', H:i:s' : '')).' '.strtoupper($timestamp->timezone->getAbbreviatedName($timestamp->isDST())).'">'.$timestamp->diffForHumans().'</abbr>';
}

/**
 * Formats a number to fit the number of digits given,
 * for generating masterlist numbers.
 *
 * @param mixed $number
 * @param mixed $digits
 *
 * @return string
 */
function format_masterlist_number($number, $digits) {
    return sprintf('%0'.$digits.'d', $number);
}

/**
 * Parses a piece of user-entered text for HTML output and optionally gets pings.
 *
 * @param string $text
 * @param array  $pings
 *
 * @return string
 */
function parse($text, &$pings = null) {
    if (!$text) {
        return null;
    }

    require_once base_path().'/vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';

    $config = HTMLPurifier_Config::createDefault();
    $config->set('Attr.EnableID', true);
    $config->set('HTML.DefinitionID', 'include');
    $config->set('HTML.DefinitionRev', 2);
    if ($def = $config->maybeGetRawHTMLDefinition()) {
        $def->addElement('include', 'Block', 'Empty', 'Common', ['file*' => 'URI', 'height' => 'Text', 'width' => 'Text']);
        $def->addAttribute('a', 'data-toggle', 'Enum#collapse,tab');
        $def->addAttribute('a', 'aria-expanded', 'Enum#true,false');
        $def->addAttribute('a', 'data-target', 'Text');
        $def->addAttribute('div', 'data-parent', 'Text');
    }

    $purifier = new HTMLPurifier($config);
    $text = $purifier->purify($text);

    $users = $characters = null;
    $text = parseUsers($text, $users);
    $text = parseUsersAndAvatars($text, $users);
    $text = parseUserIDs($text, $users);
    $text = parseUserIDsForAvatars($text, $users);
    $text = parseCharacters($text, $characters);
    $text = parseCharacterThumbs($text, $characters);
    $text = parseGalleryThumbs($text, $submissions);
    $text = parseItems($text, $items);
    $text = parseTraitThumbs($text, $traits);
    $text = parseItemThumbs($text, $items);
    $text = parsePrompts($text, $prompts);
    $text = parsePromptThumbs($text, $prompts);
    // $text = parseRarityThumbs($text, $rarities);
    // $text = parseSpeciesThumbs($text, $specieses);
    // $text = parseSubtypeThumbs($text, $subtypes);
    // $text = parseShopThumbs($text, $shops);
    // $text = parseCurrencyThumbs($text, $currencies);
    // $text = parseCharacterCategoryThumbs($text, $charactercategories);
    // $text = parsePromptCategoryThumbs($text, $promptcategories);
    // $text = parseTraitCategoryThumbs($text, $traitcategories);
    // $text = parseItemCategoryThumbs($text, $itemcategories);
    if ($pings) {
        $pings = ['users' => $users, 'characters' => $characters];
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match user mentions
 * and replace with a link.
 *
 * @param string $text
 * @param mixed  $users
 *
 * @return string
 */
function parseUsers($text, &$users) {
    $matches = null;
    $users = [];
    $count = preg_match_all('/\B@([A-Za-z0-9_-]+)/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $user = App\Models\User\User::where('name', $match)->first();
            if ($user) {
                $users[] = $user;
                $text = preg_replace('/\B@'.$match.'/', $user->displayName, $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match user mentions
 * and replace with a link and avatar.
 *
 * @param string $text
 * @param mixed  $users
 *
 * @return string
 */
function parseUsersAndAvatars($text, &$users) {
    $matches = null;
    $users = [];
    $count = preg_match_all('/\B%([A-Za-z0-9_-]+)/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $user = App\Models\User\User::where('name', $match)->first();
            if ($user) {
                $users[] = $user;
                $text = preg_replace('/\B%'.$match.'/', '<a href="'.$user->url.'"><img src="'.$user->avatarUrl.'" style="width:70px; height:70px; border-radius:50%; " alt="'.$user->name.'\'s Avatar"></a>'.$user->displayName, $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match userid mentions
 * and replace with a link.
 *
 * @param string $text
 * @param mixed  $users
 *
 * @return string
 */
function parseUserIDs($text, &$users) {
    $matches = null;
    $users = [];
    $count = preg_match_all('/\[user=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $user = App\Models\User\User::where('id', $match)->first();
            if ($user) {
                $users[] = $user;
                $text = preg_replace('/\[user='.$match.'\]/', $user->displayName, $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match userid mentions
 * and replace with a user avatar.
 *
 * @param string $text
 * @param mixed  $users
 *
 * @return string
 */
function parseUserIDsForAvatars($text, &$users) {
    $matches = null;
    $users = [];
    $count = preg_match_all('/\[userav=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $user = App\Models\User\User::where('id', $match)->first();
            if ($user) {
                $users[] = $user;
                $text = preg_replace('/\[userav='.$match.'\]/', '<a href="'.$user->url.'"><img src="'.$user->avatarUrl.'" style="width:70px; height:70px; border-radius:50%; " alt="'.$user->name.'\'s Avatar"></a>', $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match character mentions
 * and replace with a link.
 *
 * @param string $text
 * @param mixed  $characters
 *
 * @return string
 */
function parseCharacters($text, &$characters) {
    $matches = null;
    $characters = [];
    $count = preg_match_all('/\[character=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $character = App\Models\Character\Character::where('slug', $match)->first();
            if ($character) {
                $characters[] = $character;
                $text = preg_replace('/\[character='.$match.'\]/', $character->displayName, $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match character mentions
 * and replace with a thumbnail.
 *
 * @param string $text
 * @param mixed  $characters
 *
 * @return string
 */
function parseCharacterThumbs($text, &$characters) {
    $matches = null;
    $characters = [];
    $count = preg_match_all('/\[charthumb=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $character = App\Models\Character\Character::where('slug', $match)->first();
            if ($character) {
                $characters[] = $character;
                $text = preg_replace('/\[charthumb='.$match.'\]/', '<a href="'.$character->url.'"><img class="img-thumbnail" alt="Thumbnail of '.$character->fullName.'" data-toggle="tooltip" title="'.$character->fullName.'" src="'.$character->image->thumbnailUrl.'"></a>', $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match item mentions
 * and replace with a thumbnail link.
 *
 * @param string $text
 * @param mixed  $items
 *
 * @return string
 */
function parseItems($text, &$items) {
    $matches = null;
    $items = [];
    $count = preg_match_all('/\[item=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $item = App\Models\Item\Item::where('id', $match)->first();
            if ($item) {
                $items[] = $item;
                $text = preg_replace('/\[item='.$match.'\]/', '<a href="'.$item->idUrl.'"><img src="'.$item->imageUrl.'" class="mw-100" alt="'.$item->name.'"></a>', $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match trait mentions
 * and replace with the trait thumbnail.
 *
 * @param string $text
 * @param mixed  $traits
 *
 * @return string
 */
function parseTraitThumbs($text, &$traits) {
    $matches = null;
    $traits = [];
    $count = preg_match_all('/\[traitthumb=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $trait = App\Models\Feature\Feature::where('id', $match)->first();
            if ($trait) {
                $traits[] = $trait;
                $trait_hasimg = $trait->has_image ? '<a class="badge" style="border-radius:.5em; '.$traitbg.'" href="'.$trait->url.'"><img class="my-1 modal-image" style="max-height:100%; height:150px; border-radius:.5em;" src="'.$trait->imageUrl.'" alt="'.$trait->name.'" /></a></ br>' : '';
                $traitbg = $trait->rarity->color ? 'background-color:#'.$trait->rarity->color : '';
                $text = preg_replace('/\[traitthumb='.$match.'\]/', '<div class="text-center align-self-center inventory-item px-1" style="display: inline-flex; max-width:15%; flex: 0 0 15%;"><p>'.$trait_hasimg.'<a class="display-trait" href="'.$trait->url.'">'.$trait->name.'</a></p></div>', $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match item mentions
 * and replace with the item thumbnail.
 *
 * @param string $text
 * @param mixed  $items
 *
 * @return string
 */
function parseItemThumbs($text, &$items) {
    $matches = null;
    $items = [];
    $count = preg_match_all('/\[itemthumb=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $item = App\Models\Item\Item::where('id', $match)->first();
            if ($item) {
                $items[] = $item;
                $item_hasimg = $item->has_image ? '<a class="badge" style="border-radius:.5em;" href="'.$item->idUrl.'"><img class="my-1 modal-image" style="max-height:100%; height:150px; border-radius:.5em;" src="'.$item->imageUrl.'" alt="'.$item->name.'" /></a></ br>' : '';
                $text = preg_replace('/\[itemthumb='.$match.'\]/', '<div class="text-center align-self-center inventory-item px-1" style="display: inline-flex; max-width:15%; flex: 0 0 15%;"><p>'.$item_hasimg.'<a class="inventory-stack inventory-stack-name" href="'.$item->idUrl.'">'.$item->name.'</a></p></div>', $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match prompt mentions
 * and replace with the prompt image.
 *
 * @param string $text
 * @param mixed  $prompts
 *
 * @return string
 */
function parsePrompts($text, &$prompts) {
    $matches = null;
    $prompts = [];
    $count = preg_match_all('/\[prompt=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $prompt = App\Models\Prompt\Prompt::where('id', $match)->first();
            if ($prompt) {
                $prompts[] = $prompt;
                $text = preg_replace('/\[prompt='.$match.'\]/', '<a href="'.$prompt->idUrl.'"><img src="'.$prompt->imageUrl.'" class="mw-100" alt="'.$prompt->name.'"></a>', $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match prompt mentions
 * and replace with the prompt thumbnail.
 *
 * @param string $text
 * @param mixed  $prompts
 *
 * @return string
 */
function parsePromptThumbs($text, &$prompts) {
    $matches = null;
    $prompts = [];
    $count = preg_match_all('/\[promptthumb=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $prompt = App\Models\Prompt\Prompt::where('id', $match)->first();
            if ($prompt) {
                $prompts[] = $prompt;
                $prompt_hasimg = $prompt->has_image ? '<a class="badge" style="border-radius:.5em;" href="'.$prompt->idUrl.'"><img class="my-1 modal-image" style="max-height:100%; height:150px; border-radius:.5em;" src="'.$prompt->imageUrl.'" alt="'.$prompt->name.'" /></a></ br>' : '';
                $text = preg_replace('/\[promptthumb='.$match.'\]/', '<div class="text-center align-self-center inventory-item px-1" style="display: inline-flex; max-width:15%; flex: 0 0 15%;"><p>'.$prompt_hasimg.'<a class="inventory-stack inventory-stack-name" href="prompts/'.$prompt->id.'">'.$prompt->name.'</a></p></div>', $text);
            }
        }
    }

    return $text;
}

/**
 * Parses a piece of user-entered text to match gallery submission thumb mentions
 * and replace with a link.
 *
 * @param string $text
 * @param mixed  $submissions
 *
 * @return string
 */
function parseGalleryThumbs($text, &$submissions) {
    $matches = null;
    $submissions = [];
    $count = preg_match_all('/\[thumb=([^\[\]&<>?"\']+)\]/', $text, $matches);
    if ($count) {
        $matches = array_unique($matches[1]);
        foreach ($matches as $match) {
            $submission = App\Models\Gallery\GallerySubmission::where('id', $match)->first();
            if ($submission) {
                $submissions[] = $submission;
                $text = preg_replace('/\[thumb='.$match.'\]/', '<a href="'.$submission->url.'" data-toggle="tooltip" title="'.$submission->displayTitle.' by '.nl2br(htmlentities($submission->creditsPlain)).(isset($submission->content_warning) ? '<br/><strong>Content Warning:</strong> '.nl2br(htmlentities($submission->content_warning)) : '').'">'.view('widgets._gallery_thumb', ['submission' => $submission]).'</a>', $text);
            }
        }
    }

    return $text;
}

/**
 * Generates a string of random characters of the specified length.
 *
 * @param int $characters
 *
 * @return string
 */
function randomString($characters) {
    $src = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $code = '';
    for ($i = 0; $i < $characters; $i++) {
        $code .= $src[mt_rand(0, strlen($src) - 1)];
    }

    return $code;
}

/**
 * Check that a url is from a site used for authentication,
 * and if it belongs to a user.
 *
 * @param string $url
 * @param bool   $failOnError
 *
 * @return App\Models\User\User|string
 */
function checkAlias($url, $failOnError = true) {
    if ($url) {
        $recipient = null;
        $matches = [];
        // Check to see if url is 1. from a site used for auth
        foreach (config('lorekeeper.sites') as $key=> $site) {
            if (isset($site['auth']) && $site['auth']) {
                preg_match_all($site['regex'], $url, $matches, PREG_SET_ORDER, 0);
                if ($matches != []) {
                    $urlSite = $key;
                    break;
                }
            }
        }
        if ((!isset($matches[0]) || $matches[0] == []) && $failOnError) {
            throw new Exception('This URL is from an invalid site. Please provide a URL for a user profile from a site used for authentication.');
        }

        // and 2. if it contains an alias associated with a user on-site.

        if (isset($matches[0]) && $matches[0] != [] && isset($matches[0][1])) {
            if ($urlSite != 'discord') {
                $alias = App\Models\User\UserAlias::where('site', $urlSite)->where('alias', $matches[0][1])->first();
            } else {
                $alias = App\Models\User\UserAlias::where('site', $urlSite)->where('alias', $matches[0][0])->first();
            }
            if ($alias) {
                $recipient = $alias->user;
            } else {
                $recipient = $url;
            }
        }

        return $recipient;
    }
}

/**
 * Prettifies links to user profiles on various sites in a "user@site" format.
 *
 * @param string $url
 *
 * @return string
 */
function prettyProfileLink($url) {
    $matches = [];
    // Check different sites and return site if a match is made, plus username (retreived from the URL)
    foreach (config('lorekeeper.sites') as $siteName=> $siteInfo) {
        if (preg_match_all($siteInfo['regex'], $url, $matches)) {
            $site = $siteName;
            $name = $matches[1][0];
            $link = $matches[0][0];
            $icon = $siteInfo['icon'] ?? 'fas fa-globe';
            break;
        }
    }

    // Return formatted link if possible; failing that, an unformatted link
    if (isset($name) && isset($site) && isset($link)) {
        return '<a href="https://'.$link.'"><i class="'.$icon.' mr-1" style="opacity: 50%;"></i>'.$name.'@'.(config('lorekeeper.sites.'.$site.'.display_name') != null ? config('lorekeeper.sites.'.$site.'.display_name') : $site).'</a>';
    } else {
        return '<a href="'.$url.'"><i class="fas fa-globe mr-1" style="opacity: 50%;"></i>'.$url.'</a>';
    }
}

/**
 * Prettifies user profile names for use in various functions.
 *
 * @param string $url
 *
 * @return string
 */
function prettyProfileName($url) {
    $matches = [];
    // Check different sites and return site if a match is made, plus username (retreived from the URL)
    foreach (config('lorekeeper.sites') as $siteName=> $siteInfo) {
        if (preg_match_all($siteInfo['regex'], $url, $matches)) {
            $site = $siteName;
            $name = $matches[1][0];
            break;
        }
    }

    // Return formatted name if possible; failing that, an unformatted url
    if (isset($name) && isset($site)) {
        return $name.'@'.(config('lorekeeper.sites.'.$site.'.display_name') != null ? config('lorekeeper.sites.'.$site.'.display_name') : $site);
    } else {
        return $url;
    }
}

/**
 * Checks the site setting and returns the appropriate FontAwesome version.
 *
 * @return string
 */
function faVersion() {
    $setting = config('lorekeeper.settings.fa_version');
    $directory = 'css';

    switch ($setting) {
        case 0:
            $version = 'allv5';
            break;
        case 1:
            $version = 'allv6';
            break;
        case 2:
            $version = 'allvmix';
            break;
    }

    return asset($directory.'/'.$version.'.min.css');
}

/**
 * Returns the given objects limits, if any.
 *
 * @param mixed $object
 *
 * @return bool
 */
function getLimits($object) {
    return App\Models\Limit\Limit::where('object_model', get_class($object))->where('object_id', $object->id)->get();
}

/**
 * checks if a certain object has any limits.
 *
 * @param mixed $object
 */
function hasLimits($object) {
    return App\Models\Limit\Limit::where('object_model', get_class($object))->where('object_id', $object->id)->exists();
}

/**
 * Checks if a user has a limit unlocked.
 *
 * @param mixed $object
 * @param mixed $user
 */
function hasUnlockedLimits($user, $object) {
    if (!hasLimits($object)) {
        return true;
    }

    return App\Models\Limit\UserUnlockedLimit::where('user_id', $user->id)
        ->where('object_model', get_class($object))
        ->where('object_id', $object->id)
        ->exists();
}

/**
 * Returns the given objects rewards, if any.
 *
 * @param mixed $object
 *
 * @return bool
 */
function getRewards($object) {
    return App\Models\Reward\Reward::where('object_model', get_class($object))->where('object_id', $object->id)->get();
}

/**
 * checks if a certain object has any rewards.
 *
 * @param mixed $object
 */
function hasRewards($object) {
    return App\Models\Reward\Reward::where('object_model', get_class($object))->where('object_id', $object->id)->exists();
}
