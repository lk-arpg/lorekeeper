@extends('layouts.app')

@section('title')
    Credits
@endsection

@section('content')
    {!! breadcrumbs(['Credits' => url('credits')]) !!}
    <h1>Credits</h1>

    <div class="site-page-content parsed-text">
        {!! $credits->parsed_text !!}
    </div>

    <hr>

    <h4 class="mb-0">Core Extensions</h4>
    <p class="mb-2">These extensions were coded by the Lorekeeper community and are now a part of core Lorekeeper.</p>
    <div class="extensions row no-gutters">
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Admin_Edit_Button"><strong>Admin Edit Buttons</strong></a> by <a href="https://github.com/preimpression/">Preimpression</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Alias_Logins"><strong>Alias Logins</strong></a> by <a href="https://github.com/AW0005">Moif</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Bootstrap_Tables"><strong>Bootstrap Tables</strong></a> by <a href="https://github.com/preimpression">Preimpression</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Character_Items"><strong>Character Items</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Character_Warnings"><strong>Character Warnings</strong></a> by <a href="https://github.com/redcr0wz">Lexi</a>, <a href="https://github.com/SpeedyD">Speedy</a>, <a
                href="https://github.com/ScuffedNewt">ScuffedNewt</a>, & <a href="https://github.com/itinerare">itinerare</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Comments"><strong>Comments</strong></a> by <a href="https://github.com/preimpression">Preimpression</a> & <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Deactivate_Account"><strong>Deactivate Account</strong></a> by <a href="https://github.com/preimpression">Preimpression</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Dynamic_Limits"><strong>Dynamic Limits</strong></a> by <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Dynamic_Rewards"><strong>Dynamic Rewards</strong></a> by <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Extended_Mentions"><strong>Extended Mentions</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Extension_Tracker"><strong>Extension Tracker</strong></a> by <a href="https://github.com/preimpression">Preimpression</a> (This page/the section below!)
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Galleries"><strong>Galleries</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Log_Viewer"><strong>Log Viewer</strong></a> by <a href="https://github.com/Cylunny">Cylunny</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Masterlist_Sublists"><strong>Masterlist Sublists</strong></a> by <a href="https://github.com/junijwi">Junijwi</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="https://wiki.lorekeeper.me/index.php?title=Extensions:Multiple_Trait_Subtypes"><strong>Multiple Trait Subtypes</strong></a> by <a href="https://github.com/Draconizations">Fulmine</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:MYO_Item_Tag"><strong>MYO Item Tag</strong></a> by <a href="https://github.com/junijwi">Junijwi</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Pagination_Page_Select"><strong>Pagination Page Selector</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a> and <a href="https://github.com/AW0005">Moif</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Reports"><strong>Reports</strong></a> by <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Separate_Prompts"><strong>Separate Prompts</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Shop_Features"><strong>Shop Features</strong></a> by <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Stacked_Inventories"><strong>Stacked Inventories</strong></a> by <a href="https://github.com/Draginraptor">Draginraptor</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Submission_Addons"><strong>Submission Addons</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Submission_Drafts"><strong>Submission Drafts</strong></a> by <a href="https://github.com/preimpression/">Preimpression</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Touchscreen_Sortable"><strong>Touchscreen Sortable</strong></a> by <a href="https://github.com/liwoyadan">liwoyadan</a> (via <a
                href="https://github.com/furf/jquery-ui-touch-punch">Touch Punch</a>)
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:User_Transfer_Reasons"><strong>User Transfer Reasons</strong></a> by <a href="https://github.com/snupsplus">Snupsplus</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Shop_Features"><strong>Shop Features</strong></a> by <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Dynamic_Limits"><strong>Dynamic Limits</strong></a> by <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:TinyMCE_Code_Editor"><strong>TinyMCE Code Editor</strong></a> by <a href="https://github.com/AW0005">Moif</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Trade_Listings"><strong>Trade Listings</strong></a> by <a href="https://github.com/itinerare">itinerare</a> & <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Aliases_on_Userpage"><strong>Aliases on Userpage</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
            ({{ config('lorekeeper.extensions.aliases_on_userpage') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Kitchen_Sink_Index"><strong>All Traits Index</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
            ({{ config('lorekeeper.extensions.visual_trait_index.enable_all_trait_index') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Autopopulate_New_Image_Traits"><strong>Auto-populate New Image Traits</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
            ({{ config('lorekeeper.extensions.autopopulate_image_features') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Captcha"><strong>Captcha</strong> (v3)</a> by <a href="https://github.com/itinerare">itinerare</a>
            ({{ config('lorekeeper.extensions.use_recaptcha') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Character_Prev_and_Next"><strong>Character Prev and Next</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
            ({{ config('lorekeeper.extensions.previous_and_next_characters.display') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Character_Rewards"><strong>Character Rewards</strong></a> by <a href="https://github.com/preimpression">Preimpression</a>
            ({{ config('lorekeeper.extensions.character_reward_expansion.expanded') ? 'Enabled' : 'Disabled' }}/{{ config('lorekeeper.extensions.character_reward_expansion.default_recipient') ? 'Submitter' : 'Character Owner' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Character_Status_Badges"><strong>Character Status Badges</strong></a> by <a href="https://github.com/junijwi">Junijwi</a>
            ({{ config('lorekeeper.extensions.character_status_badges') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Character_TH_Profile_Link"><strong>Character TH Profile Link</strong></a> by <a href="https://github.com/junijwi">Junijwi</a>
            ({{ config('lorekeeper.extensions.character_TH_profile_link') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Collapsible_Admin_Sidebar"><strong>Collapsible Admin Sidebar</strong></a> by <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
            ({{ config('lorekeeper.extensions.collapsible_admin_sidebar') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Design_Update_Voting"><strong>Design Update Voting</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
            ({{ config('lorekeeper.extensions.design_update_voting') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Gravatar"><strong>Gravatar</strong></a> by <a href="https://github.com/ScuffedNewt">ScuffedNewt</a>
            ({{ config('lorekeeper.extensions.use_gravatar') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Group_Traits_by_Category"><strong>Group Traits by Category</strong></a> by <a href="https://github.com/preimpression">Preimpression</a>
            ({{ config('lorekeeper.extensions.traits_by_category') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Item_Entry_Expansion"><strong>Item Entry Expansion</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
            ({{ config('lorekeeper.extensions.item_entry_expansion.extra_fields') ? 'Enabled' : 'Disabled' }}/{{ config('lorekeeper.extensions.item_entry_expansion.resale_function') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:MYO_Image_Removal"><strong>MYO Image Removal</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
            ({{ config('lorekeeper.extensions.remove_myo_image') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Navbar_News_Notif"><strong>Navbar News Notif</strong></a> by <a href="https://github.com/junijwi">Junijwi</a>
            ({{ config('lorekeeper.extensions.navbar_news_notif') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Organised_Traits_Dropdown"><strong>Organised Traits Dropdown</strong></a> by <a href="https://github.com/draginraptor">Draginraptor</a> and <a
                href="https://github.com/ScuffedNewt">ScuffedNewt</a>
            ({{ config('lorekeeper.extensions.organised_traits_dropdown.enable') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Scroll_To_Top"><strong>Scroll to Top</strong></a> by <a href="https://github.com/preimpression">Preimpression</a>
            ({{ config('lorekeeper.extensions.scroll_to_top') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Gallery_Recent_Submissions"><strong>Show All Recent Submissions</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
            ({{ config('lorekeeper.extensions.show_all_recent_submissions.enable') ? 'Enabled' . ((config('lorekeeper.extensions.show_all_recent_submissions.links.sidebar') && config('lorekeeper.extensions.show_all_recent_submissions.links.indexbutton') ? '/Both' : (config('lorekeeper.extensions.show_all_recent_submissions.links.sidebar') ? '/Sidebar' : '') . (config('lorekeeper.extensions.show_all_recent_submissions.links.indexbutton') ? '/Button' : '')) . (config('lorekeeper.extensions.show_all_recent_submissions.section_on_front') ? '/Front' : '')) : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Species_Trait_Index"><strong>Species Trait Index</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
            ({{ config('lorekeeper.extensions.visual_trait_index.enable_species_index') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Subtype_Trait_Index"><strong>Subtype Trait Index</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
            ({{ config('lorekeeper.extensions.visual_trait_index.enable_subtype_index') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Staff_Rewards"><strong>Staff Rewards</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
            ({{ config('lorekeeper.extensions.staff_rewards.enabled') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:TinyMCE_Code_Editor"><strong>TinyMCE Code Editor</strong></a> by <a href="https://github.com/AW0005">Moif</a>
            ({{ config('lorekeeper.extensions.tinymce_code_editor') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Universal_Trait_Index"><strong>Universal Trait Index</strong></a> by <a href="https://github.com/CH3RVB">CH3RVB</a>
            ({{ config('lorekeeper.extensions.visual_trait_index.enable_universal_index') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:User_Character_Minibadges"><strong>User Character Minibadges</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
            ({{ config('lorekeeper.extensions.badges_on_user_character_page') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:VTI_Trait_Modals"><strong>Visual Trait Indexes Trait Modals</strong></a> by <a href="https://github.com/AW0005">Moif</a>
            ({{ config('lorekeeper.extensions.visual_trait_index.trait_modals') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Watermarking"><strong>Watermarking</strong></a> by <a href="https://github.com/itinerare">itinerare</a>
            ({{ config('lorekeeper.settings.watermark_masterlist_images') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:Watermarking_Image_Automation"><strong>Watermarking - Image Automation</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
            ({{ config('lorekeeper.settings.masterlist_image_automation') ? 'Enabled' : 'Disabled' }})
        </p>
        <p class="mb-0 col-md-4">
            <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:90_Percent_Watermark"><strong>Watermark Resizing</strong></a> by <a href="https://github.com/SpeedyD">Speedy</a>
            ({{ config('lorekeeper.settings.watermark_resizing') ? 'Enabled' : 'Disabled' }})/({{ config('lorekeeper.settings.watermark_resizing_thumb') ? 'Enabled' : 'Disabled' }})
        </p>
    </div>

    <hr />
    <h4 class="mb-0">Installed Extensions</h4>
    <p class="mb-2">These extensions have been added to this site.</p>

    @if ($extensions->count())
        <div class="extensions row no-gutters">
            @foreach ($extensions as $extension)
                <p class="mb-0 col-md-4">
                    <a href="http://wiki.lorekeeper.me/index.php?title=Extensions:{{ $extension->wiki_key }}">
                        <strong>{{ str_replace('_', ' ', $extension->wiki_key) }}</strong>
                        <small>v. {{ $extension->version }}</small>
                    </a>
                    by
                    <?php $extension->array = json_decode($extension->creators, true);
                    $extension->end = end($extension->array); ?>
                    @foreach (json_decode($extension->creators, true) as $name => $url)
                        <a href="{{ $url }}">{{ $name }}</a>{{ $extension->end != $extension->array[$name] ? ',' : '' }}
                    @endforeach
                </p>
            @endforeach
        </div>
    @else
        <p>No extensions found.</p>
    @endif

@endsection
