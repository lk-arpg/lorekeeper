<ul>
    @foreach ($characters as $character)
        <li>
            <a href="{{ $character->url }}">{{ $character->fullName }}</a> has {{ $characterItems->where('character_id', $character->id)->pluck('count')->sum() }}
        </li>
    @endforeach
</ul>
