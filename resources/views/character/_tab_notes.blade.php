@if ($character->parsed_description)
    <div class="parsed-text descriptioneditingparse">{!! $character->parsed_description !!}</div>
@else
    <div class="parsed-text descriptioneditingparse">No description given.</div>
@endif
@if (Auth::check() && Auth::user()->hasPower('manage_characters'))
    <div class="mt-3">
        <a href="#" class="btn btn-outline-info btn-sm edit-description" data-{{ $character->is_myo_slot ? 'id' : 'slug' }}="{{ $character->is_myo_slot ? $character->id : $character->slug }}"><i class="fas fa-cog"></i> Edit Description</a>
    </div>
@endif
