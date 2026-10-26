@if ($prompt)
    @php
        $count = $prompt->getCount($character->user, collect($character));
    @endphp
    @if ($prompt->limit && $prompt->limit_character)
        <div class="text-danger">
            {{ $character->fullName . ' can be included on this prompt ' . $prompt->limit . ' time(s)' }}
            {{ $prompt->limit_period ? ' per ' . strtolower(config('lorekeeper.extensions.limit_periods')[$prompt->limit_period]) : '' }}
        </div>
        <div>
            {{ $character->fullName . ' has submitted this prompt ' . $count[$prompt->limit_period] . ' times(s) within this timeframe' }}</p>
        </div>
    @else
        <div>
            {{ $character->fullName . ' can be included on this prompt an unlimited number of times.' }}<br>
            {{ $character->fullName . ' has submitted this prompt ' . $count['all'] . ' times(s) total.' }}</div>
    @endif
@endif
