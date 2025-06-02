@if ($character)
    <div class="character-info" data-id="{{ $character->id }}"><img src="{{ $character->image->thumbnailUrl }}" class="mw-100" alt="Thumbnail for {{ $character->fullName }}" /></div>
    <div class="text-center"><a href="{{ $character->url }}">{{ $character->slug }}</a></div>
    @if (!$character->is_visible && Auth::check() && Auth::user()->isStaff)
        <div class="text-danger character-info" data-id="0"><i class="fas fa-eye-slash me-1"></i> Character hidden from public view.</div>
    @endif
@else
    <div class="text-danger character-info" data-id="0">Character not found.</div>
@endif
