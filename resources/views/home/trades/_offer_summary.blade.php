@if ($data)
    @if ($data['user_items'])
        <div class="row">
            @foreach ($stacks as $stack)
                <div class="col-sm-3 col-4 mb-3" title="{{ $stack->first()->item->name }}" data-bs-toggle="tooltip">
                    <div class="text-center inventory-item">
                        <div class="mb-1">
                            @if (isset($stack->first()->item->imageUrl))
                                <a class="inventory-stack"><img src="{{ $stack->first()->item->imageUrl }}" class="mw-100" alt="{{ $stack->first()->item->name }}" /></a>
                            @else
                                {{ $stack->first()->item->name }}
                            @endif
                            x{{ $stack->sum('quantity') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    @if ($data['characters'])
        <div class="row">
            @foreach ($data['characters'] as $character)
                <div class="col-sm-3 col-4 mb-3">
                    <div class="text-center inventory-item">
                        <div class="mb-1">
                            <a class="inventory-stack"><img src="{{ $character['asset']->image->thumbnailUrl }}" class="img-thumbnail" title="{{ $character['asset']->fullName }}" data-bs-toggle="tooltip"
                                    alt="Thumbnail for {{ $character['asset']->fullName }}" /></a>
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
@endif
