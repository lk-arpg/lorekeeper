@if ($data && collect($data)->filter()->isNotEmpty())
    @if ($data['user_items'])
        <div class="row">
            @foreach ($stacks as $stack)
                <div class="col-sm-6 col-md-4 col-12 mb-3" title="{{ $stack->first()->item->name }}" data-toggle="tooltip">
                    <div class="text-center inventory-item">
                        @if (isset($stack->first()->item->imageUrl))
                            <img src="{{ $stack->first()->item->imageUrl }}" class="img-fluid" alt="{{ $stack->first()->item->name }}" />
                        @endif
                        {{ $stack->first()->item->name }} x{{ $stack->sum('quantity') }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    @if ($data['characters'])
        <div class="row">
            @foreach ($data['characters'] as $character)
                <div class="col-sm-6 col-md-4 col-12 mb-3">
                    <div class="text-center inventory-item">
                        <div class="mb-1">
                            <a class="inventory-stack">
                                <img src="{{ $character['asset']->image->thumbnailUrl }}" class="img-thumbnail" title="{{ $character['asset']->fullName }}" data-toggle="tooltip" alt="Thumbnail for {{ $character['asset']->fullName }}" />
                                {!! $character['asset']->displayName !!}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    @if ($data['currencies'])
        <div>
            @foreach ($data['currencies'] as $currency)
                <div>
                    {!! $currency['asset']->display($currency['quantity']) !!}
                </div>
            @endforeach
        </div>
    @endif
@else
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No assets in this offer.
    </div>
@endif
