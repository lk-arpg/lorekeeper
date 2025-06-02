@if ($character)
    <div class="character-info float-start me-2" data-id="{{ $character->id }}"><img src="{{ $character->image->thumbnailUrl }}" style="height:40px;" alt="Thumbnail image for {{ $character->fullName }}" /></div>
    <div><a href="{{ $character->url }}">{{ isset($character->name) ? $character->fullName : $character->slug }}</a></div>
@else
    <div class="text-danger character-info" data-id="0">Character not found.</div>
@endif
