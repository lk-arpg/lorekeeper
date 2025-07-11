@if ($characters->count())
    <div class="row">
        @foreach ($characters as $character)
            <div class="col-md-3 col-6 text-center mb-2">
                <div>
                    <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" class="img-thumbnail {{ $character->image->showContentWarnings(Auth::user() ?? null) ? 'content-warning' : '' }}"
                            alt="Thumbnail for {{ $character->fullName }}" /></a>
                </div>
                <div class="mt-1">
                    <a href="{{ $character->url }}" class="h5 mb-0">
                        @if (!$character->is_visible)
                            <i class="fas fa-eye-slash"></i>
                        @endif {!! $character->warnings !!} {{ Illuminate\Support\Str::limit($character->fullName, 20, $end = '...') }}
                    </a>
                </div>
                <div class="small">
                    {!! $character->image->species_id ? $character->image->species->displayName : 'No Species' !!} ・ {!! $character->image->rarity_id ? $character->image->rarity->displayName : 'No Rarity' !!}{!! !$owner ? '・ ' . $character->displayOwner : null !!}{!! config('lorekeeper.extensions.badges_on_user_character_page') ? $character->miniBadge : '' !!}
                    @if ($userpage_exts)
                        {{-- Add potential extra extension data in here that applies only to the character if owned by the user. --}}
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <p>No {{ $myo ? 'MYO slots' : 'characters' }} found.</p>
@endif
