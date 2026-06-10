@php
    $totalCurrencies = [];
@endphp
@if (isset($totals))
    @if (Auth::user()->hasPower('manage_submissions') && isset($totals))
        @if (Settings::get('gallery_rewards_divided') && $collaboratorsCount > 1)
            <div class="alert alert-info">Rewards are currently set to be divided among collaborators. If you would like to change this, please adjust the setting in the admin panel.</div>
        @endif
        <div>
            <h4>Calculated Totals:</h4>
            @foreach ($totals as $total)
                @php
                    if (!isset($totalCurrencies[$total['currency']->id])) {
                        $totalCurrencies[$total['currency']->id] = [
                            'currency' => $total['currency'],
                            'value' => 0,
                        ];
                    }
                    $totalCurrencies[$total['currency']->id]['value'] += $total['value'];
                @endphp
                <div class="d-flex">
                    <h5 class="mr-2">{{ $total['name'] }}: </h5>
                    <span>
                        {!! $total['currency']->display($total['value']) !!}
                    </span>
                </div>
            @endforEach
            <hr />
            <h4>Total Rewards:</h4>
            {!! implode(
                ', ',
                array_map(function ($obj) {
                    return $obj['currency']->display($obj['value']);
                }, $totalCurrencies),
            ) !!}
            @if ($collaboratorsCount > 1 && Settings::get('gallery_rewards_divided') === '1')
                <br />Divided Among {{ $collaboratorsCount }} {{ str_plural('Collaborator', $collaboratorsCount) }}
            @elseif ($collaboratorsCount > 1)
                <br />Awarded to each of {{ $collaboratorsCount }} {{ str_plural('Collaborator', $collaboratorsCount) }}
            @endif
        </div>
    @endif
@else
    <p>This submission does not have form data associated with it.</p>
@endif
