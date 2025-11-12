@if ($data)
    @if ($data['user_items'])
        <div class="row mb-2">
            <div class="col-sm-2">
                <strong>Items:</strong>
            </div>
            <div class="col-md">
                <div class="row">
                    @foreach ($data['user_items'] as $itemRow)
                        <div class="col-sm-4">
                            @if (isset($itemRow['asset']->item->imageUrl))
                                <img class="small-icon" src="{{ $itemRow['asset']->item->imageUrl }}">
                            @endif
                            <a href="{{ $itemRow['asset']->item->url }}">{!! $itemRow['asset']->item->displayName !!}</a> x{!! $itemRow['quantity'] !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    @if ($data['characters'])
        <div class="row mb-2">
            <div class="col-sm-2">
                <strong>Characters:</strong>
            </div>
            <div class="col-md">
                <div class="row">
                    @foreach ($data['characters'] as $character)
                        <div class="col-sm-4">
                            @if (isset($character['asset']->image->thumbnailUrl))
                                <img class="small-icon" src="{{ $character['asset']->image->thumbnailUrl }}">
                            @endif
                            <a href="{{ $character['asset']->url }}">{{ $character['asset']->fullName }}</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    @if ($data['currencies'])
        <div class="row">
            <div class="col-sm-2">
                <strong>Currencies:</strong>
            </div>
            <div class="col-md">
                <div class="row">
                    @foreach ($data['currencies'] as $currency)
                        <div class="col-sm-3">
                            {!! $currency['asset']->display('') !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endif
@if (isset($etc) && $etc)
    <div class="row">
        <div class="col-sm-2">
            <strong>Other:</strong>
        </div>
        <div class="col-md">
            {!! nl2br(htmlentities($etc)) !!}
        </div>
    </div>
@endif
