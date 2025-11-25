@if (!$character->is_myo_slot && config('lorekeeper.extensions.previous_and_next_characters.display') && isset($extPrevAndNextBtnsUrl))
    @if ($extPrevAndNextBtns['prevCharName'] || $extPrevAndNextBtns['nextCharName'])
        <div class="row mb-4">
            @if ($extPrevAndNextBtns['prevCharName'])
                <div class="col text-left float-left">
                    <a class="btn btn-outline-success text-success" href="{{ $extPrevAndNextBtns['prevCharUrl'] }}{!! $extPrevAndNextBtnsUrl !!}">
                        <i class="fas fa-angle-double-left"></i> Previous Character ・ <span class="text-primary">{!! $extPrevAndNextBtns['prevCharName'] !!}</span>
                    </a>
                </div>
            @endif
            @if ($extPrevAndNextBtns['nextCharName'])
                <div class="col text-right float-right">
                    <a class="btn btn-outline-success text-success" href="{{ $extPrevAndNextBtns['nextCharUrl'] }}{!! $extPrevAndNextBtnsUrl !!}">
                        <span class="text-primary">{!! $extPrevAndNextBtns['nextCharName'] !!}</span> ・ Next Character <i class="fas fa-angle-double-right"></i><br />
                    </a>
                </div>
            @endif
        </div>
    @endif
@endif
<div class="character-masterlist-categories">
    @if (!$character->is_myo_slot)
        {!! $character->category?->displayName ?? 'None' !!} ・ {!! $character->image->species?->displayName ?? 'None' !!} ・ {!! $character->image->rarity?->displayName ?? 'None' !!}
    @else
        MYO Slot @if ($character->image->species_id)
            ・ {!! $character->image->species->displayName !!}
            @endif @if ($character->image->rarity_id)
                ・ {!! $character->image->rarity->displayName !!}
            @endif
        @endif
</div>
<h1 class="mb-0">
    @if (config('lorekeeper.extensions.character_status_badges'))
        <!-- character trade/gift status badges -->
        <div class="float-right">
            <span class="btn {{ $character->is_trading ? 'badge-success' : 'badge-danger' }} float-right ml-2" data-toggle="tooltip" title="{{ $character->is_trading ? 'OPEN for sale and trade offers.' : 'CLOSED for sale and trade offers.' }}"><i
                    class="fas fa-comments-dollar"></i></span>
            @if (!$character->is_myo_slot)
                <span class="btn {{ $character->is_gift_writing_allowed == 1 ? 'badge-success' : ($character->is_gift_writing_allowed == 2 ? 'badge-warning text-light' : 'badge-danger') }} float-right ml-2" data-toggle="tooltip"
                    title="{{ $character->is_gift_writing_allowed == 1 ? 'OPEN for gift writing.' : ($character->is_gift_writing_allowed == 2 ? 'PLEASE ASK before gift writing.' : 'CLOSED for gift writing.') }}"><i class="fas fa-file-alt"></i></span>
                <span class="btn {{ $character->is_gift_art_allowed == 1 ? 'badge-success' : ($character->is_gift_art_allowed == 2 ? 'badge-warning text-light' : 'badge-danger') }} float-right ml-2" data-toggle="tooltip"
                    title="{{ $character->is_gift_art_allowed == 1 ? 'OPEN for gift art.' : ($character->is_gift_art_allowed == 2 ? 'PLEASE ASK before gift art.' : 'CLOSED for gift art.') }}"><i class="fas fa-pencil-ruler"></i></span>
            @endif
        </div>
    @endif
    @if ($character->is_visible && Auth::check() && $character->user_id != Auth::user()->id)
        <?php $bookmark = Auth::user()->hasBookmarked($character); ?>
        <a href="#" class="btn btn-outline-info float-right bookmark-button ml-2" data-id="{{ $bookmark ? $bookmark->id : 0 }}" data-character-id="{{ $character->id }}"><i class="fas fa-bookmark"></i>
            {{ $bookmark ? 'Edit Bookmark' : 'Bookmark' }}</a>
    @endif
    @if (config('lorekeeper.extensions.character_TH_profile_link') && $character->profile->link)
        <a class="btn btn-outline-info float-right" data-character-id="{{ $character->id }}" href="{{ $character->profile->link }}"><i class="fas fa-home"></i> Profile</a>
    @endif
    @if (!$character->is_visible)
        <i class="fas fa-eye-slash"></i>
    @endif
    {!! $character->displayName !!}
    @if (!$character->is_myo_slot)
        <i data-toggle="tooltip" title="Click to Copy the Character Code" id="copy" style="font-size: 14px; vertical-align: middle;" class="far fa-copy text-small"></i>
    @endif
</h1>
<div class="mb-3">
    Owned by {!! $character->displayOwner !!}
</div>


<script>
    $('#copy').on('click', async (e) => {
        await window.navigator.clipboard.writeText("{{ $character->slug }}");
        e.currentTarget.classList.remove('toCopy');
        e.currentTarget.classList.add('toCheck');
        setTimeout(() => {
            e.currentTarget.classList.remove('toCheck');
            e.currentTarget.classList.add('toCopy');
        }, 2000);
    });
</script>
