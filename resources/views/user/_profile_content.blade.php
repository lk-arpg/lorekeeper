@if ($deactivated)
    <div style="filter:grayscale(1); opacity:0.75">
@endif

<div class="row mb-3">
    <div class="col-md-2 text-center">
        <!-- User Icon -->
        <img src="{{ $user->avatarUrl }}" class="img-fluid rounded-circle" style="max-height: 125px;" alt="{{ $user->name }}'s Avatar">
    </div>

    <div class="col">
        <!-- Username & optional FTO Badge -->
        <div class="row no-gutters">
            <div class="col h2 text-center text-md-left">
                {!! $user->displayName !!}
                @if ($user->previousUsername && mb_strtolower($user->name) != mb_strtolower($user->previousUsername))
                    <small>{!! add_help('Previously known as ' . $user->previousUsername) !!}</small>
                @endif
                <a href="{{ url('reports/new?url=') . $user->url }}"><i class="fas fa-exclamation-triangle fa-xs text-danger" data-toggle="tooltip" title="Click here to report this user." style="opacity: 50%;"></i></a>
            </div>

            @if ($user->settings->is_fto)
                <div class="col-md-1 text-center">
                    <span class="btn badge-success float-md-right" data-toggle="tooltip" title="This user has not owned any characters from this world before.">FTO</span>
                </div>
            @endif
        </div>

        <!-- User Information -->
        <div class="row no-gutters">
            <div class="row no-gutters col-sm-5">
                <div class="col-lg-3 col-md-3 col-4">
                    <h5>Alias</h5>
                </div>
                <div class="col-lg-9 col-md-9 col-8">
                    {!! $user->displayAlias !!}
                    @if (count($aliases) > 1 && config('lorekeeper.extensions.aliases_on_userpage'))
                        <a class="small collapse-toggle collapsed" href="#otherUserAliases" data-toggle="collapse">&nbsp;</a>
                        <p class="collapse mb-0" id="otherUserAliases">
                            @foreach ($aliases as $alias)
                                @if ($alias != $user->primaryAlias)
                                    <a href="{{ $alias->url }}"><i class="{{ $alias->config['icon'] }} fa-fw mr-1" data-toggle="tooltip" title="{{ $alias->alias . '@' . $alias->siteDisplayName }}"></i></a>
                                @endif
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>
            <div class="row no-gutters col-sm-7">
                <div class="col-md-4 col-4">
                    <h5>Joined</h5>
                </div>
                <div class="col-md-8 col-8">{!! format_date($user->created_at, false) !!} ({{ $user->created_at->diffForHumans() }})</div>
            </div>
            <div class="row no-gutters col-sm-5">
                <div class="col-lg-3 col-md-3 col-4">
                    <h5>Rank</h5>
                </div>
                <div class="col-lg-9 col-md-9 col-8">{!! $user->rank->displayName !!} {!! $user->rank->parsed_description ? add_help($user->rank->parsed_description) : '' !!}</div>
            </div>
            @if ($user->birthdayDisplay && isset($user->birthday))
                <div class="row no-gutters col-sm-7">
                    <div class="col-md-4 col-4">
                        <h5>Birthday</h5>
                    </div>
                    <div class="col-md-8 col-8">{!! $user->birthdayDisplay !!}</div>
                </div>
            @endif
        </div>
    </div>
</div>

@if (isset($user->profile->parsed_text))
    <div class="card mb-3" style="clear:both;">
        <div class="card-body">
            {!! $user->profile->parsed_text !!}
        </div>
    </div>
@endif

<div class="card-deck mb-4 profile-assets" style="clear:both;">
    <div class="card profile-currencies profile-assets-card">
        <div class="card-body text-center">
            <h5 class="card-title">Bank</h5>
            <div class="profile-assets-content">
                @foreach ($user->getCurrencies(false, false, Auth::user() ?? null) as $currency)
                    <div>{!! $currency->display($currency->quantity) !!}</div>
                @endforeach
            </div>
            <div class="text-right"><a href="{{ $user->url . '/bank' }}">View all...</a></div>
        </div>
    </div>
    <div class="card profile-inventory profile-assets-card">
        <div class="card-body text-center">
            <h5 class="card-title">Inventory</h5>
            <div class="profile-assets-content">
                @if (count($items))
                    <div class="row">
                        @foreach ($items as $item)
                            <div class="col-md-3 col-6 profile-inventory-item">
                                @if ($item->imageUrl)
                                    <img src="{{ $item->imageUrl }}" data-toggle="tooltip" title="{{ $item->name }}" alt="{{ $item->name }}" />
                                @else
                                    <p>{{ $item->name }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div>No items owned.</div>
                @endif
            </div>
            <div class="text-right"><a href="{{ $user->url . '/inventory' }}">View all...</a></div>
        </div>
    </div>
</div>

<h2>
    <a href="{{ $user->url . '/characters' }}">Characters</a>
    @if (isset($sublists) && $sublists->count() > 0)
        @foreach ($sublists as $sublist)
            / <a href="{{ $user->url . '/sublist/' . $sublist->key }}">{{ $sublist->name }}</a>
        @endforeach
    @endif
</h2>

@foreach ($characters->take(4)->get()->chunk(4) as $chunk)
    <div class="row mb-4">
        @foreach ($chunk as $character)
            <div class="col-md-3 col-6 text-center">
                <div>
                    @if ((Auth::check() && Auth::user()->settings->content_warning_visibility == 0 && isset($character->character_warning)) || (isset($character->character_warning) && !Auth::check()))
                        <a href="{{ $character->url }}"><img src="{{ asset('images/content-warning.png') }}" class="img-thumbnail" alt="Content Warning - {{ $character->fullName }}" /></a>
                    @else
                        <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" class="img-thumbnail" alt="{{ $character->fullName }}" /></a>
                    @endif
                </div>
                <div class="mt-1">
                    <a href="{{ $character->url }}" class="h5 mb-0">
                        @if (!$character->is_visible)
                            <i class="fas fa-eye-slash"></i>
                        @endif {{ Illuminate\Support\Str::limit($character->fullName, 20, $end = '...') }}
                    </a>
                </div>
                @if ((Auth::check() && Auth::user()->settings->content_warning_visibility < 2 && isset($character->character_warning)) || (isset($character->character_warning) && !Auth::check()))
                    <div class="small">
                        <p><span class="text-danger"><strong>Character Warning:</strong></span> {!! nl2br(htmlentities($character->character_warning)) !!}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endforeach

<div class="text-right"><a href="{{ $user->url . '/characters' }}">View all...</a></div>
<hr class="mb-5" />

<div class="row col-12">
    @if ($user->settings->allow_profile_comments)
        <div class="col-md-8">
            @comments(['model' => $user->profile, 'perPage' => 5])
        </div>
    @endif
    <div class="col-md-{{ $user->settings->allow_profile_comments ? 4 : 12 }}">
        <div class="card mb-4">
            <div class="card-header" data-toggle="collapse" data-target="#mentionHelp" aria-expanded="{{ $user->settings->allow_profile_comments ? 'true' : 'false' }}">
                <h5>Mention This User</h5>
            </div>
            <div class="card-body collapse {{ $user->settings->allow_profile_comments ? 'show' : '' }}" id="mentionHelp">
                In the rich text editor:
                <div class="alert alert-secondary">
                    {{ '@' . $user->name }}
                </div>
                @if (!config('lorekeeper.settings.wysiwyg_comments'))
                    In a comment:
                    <div class="alert alert-secondary">
                        [{{ $user->name }}]({{ $user->url }})
                    </div>
                @endif
                <hr>
                <div class="my-2"><strong>For Names and Avatars:</strong></div>
                In the rich text editor:
                <div class="alert alert-secondary">
                    {{ '%' . $user->name }}
                </div>
                @if (!config('lorekeeper.settings.wysiwyg_comments'))
                    In a comment:
                    <div class="alert alert-secondary">
                        [![{{ $user->name }}'s Avatar]({{ $user->avatarUrl }})]({{ $user->url }}) [{{ $user->name }}]({{ $user->url }})
                    </div>
                @endif
            </div>
            @if (Auth::check() && Auth::user()->isStaff)
                <div class="card-footer">
                    <h5>[ADMIN]</h5>
                    Permalinking to this user, in the rich text editor:
                    <div class="alert alert-secondary">
                        [user={{ $user->id }}]
                    </div>
                    Permalinking to this user's avatar, in the rich text editor:
                    <div class="alert alert-secondary">
                        [userav={{ $user->id }}]
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if ($deactivated)
    </div>
@endif
