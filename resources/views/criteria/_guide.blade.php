@php
    $placeValue = [
        1 => 'whole',
        2 => 'tenth',
        3 => 'hundredth',
        4 => 'thousandth',
        5 => 'ten thousandth',
        6 => 'hundred thousandth',
        7 => 'millionth',
    ];
    if (!isset($isPage)) {
        $isPage = true;
    }
@endphp

<div class="{{ $isPage ? 'h1' : 'h2' }} mb-0">{{ $criterion->name }} Criterion </div>
<div class="text-secondary">{!! isset($criterion->summary) ? $criterion->summary : '' !!}</div>

<div class="text-secondary mb-4">
    Rewards {!! $criterion->currency->displayName !!}
    {!! isset($criterion->base_value) ? '<span class="mx-1"> · </span>Base Reward: ' . $criterion->currency->display($criterion->base_value) : '' !!}
    {!! $criterion->rounding !== 'No Rounding' ? '<span class="mx-1"> · </span>' . $criterion->rounding . ' to the nearest ' . $placeValue[$criterion->round_precision] . ' value.' : '' !!}
</div>
@if ($isPage)
    <p>When using this guide to calculate amounts, keep in mind that all Criterion apply onto a running total from the step before. If the criterion does not have a base reward listed above, then it starts from zero.</p>
    @foreach ($criterion->steps->where('is_active', 1) as $step)
        @php $firstOption = $step->options->first(); @endphp
        <div class="d-flex mt-3">
            <div style="flex: 2;">
                <h2 class="mb-0">{{ $step->name }}</h2>
                <div class="text-secondary">
                    {{ $step->type === 'input' ? 'Number Input' : ucfirst($step->type) }}<span class="mx-1"> · </span>{{ ucfirst($step->calc_type) }}
                    @if ($step->type === 'boolean')
                        <span class="mx-1"> · </span>{!! $criterion->currency->display($firstOption->amount) !!}
                    @endif
                </div>
                {{-- Extra info about the input is only needed if it's not multiplying by one --}}
                @if ($step->type === 'input' && !($firstOption->amount == 1 && $step->input_calc_type === 'multiplicative'))
                    <div class="text-secondary">Input value is {{ $step->input_calc_type === 'additive' ? 'added to ' : 'multiplied by ' }}{!! $criterion->currency->display($firstOption->amount) !!}</div>
                @endif
                {{-- Only display the summary if we don't have a full description --}}
                @if ($step->parsed_description)
                    <div class="parsed-text mt-3 pl-2 py-2" style="border-left: 4px solid lightgrey">{!! $step->parsed_description !!}</div>
                @elseif($step->summary)
                    <div class="parsed-text mt-3 pl-2 py-2" style="border-left: 4px solid lightgrey">{!! $step->summary !!}</div>
                @endif

                @if ($step->type === 'options')
                    <h3 class="mt-2">Options:</h3>
                @endif
            </div>
            @if ($step->has_image)
                <div class="mt-2" style="background-image: url({{ $step->imageUrl }}); background-repeat: no-repeat; background-size: contain; flex: 1;background-position-x: right;"></div>
            @endif
        </div>

        @if ($step->type === 'options')
            <div class="pl-4">
                @foreach ($step->options as $option)
                    <div class="d-flex align-items-center">
                        <h5 class="mt-2">{{ $option->name }}</h5>
                        <span class="mx-1"> · </span>
                        <div class="text-secondary">{!! $criterion->currency->display($option->amount) !!}</div>
                    </div>
                    {{-- Only display the summary if we don't have a full description --}}
                    @if ($option->parsed_description)
                        <div class="parsed-text pl-2 ml-3 py-2" style="border-left: 4px solid lightgrey">{!! $option->parsed_description !!}</div>
                    @elseif($option->summary)
                        <div class="parsed-text pl-2 ml-3 py-2" style="border-left: 4px solid lightgrey">{!! $option->summary !!}</div>
                    @endif
                @endforeach
            </div>
        @endif
    @endforeach
@else
    <p>This criterion has {{ $criterion->steps->where('is_active', 1)->count() }} option{{ $criterion->steps->where('is_active', 1)->count() != 1 ? 's' : '' }}.</p>
    <a class="float-right btn btn-outline-info" href="{{ url('world/criteria-guides/' . $criterion->id) }}">View Full Guide</a>
@endif
