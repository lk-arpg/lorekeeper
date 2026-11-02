@if ($data)
    @foreach ($data as $type => $assets)
        @if ($assets)
            <div class="row">
                <div class="col-sm-2">
                    <strong>{!! ucfirst($type) !!}:</strong>
                </div>
                <div class="col-md">
                    <div class="row">
                        @foreach ($assets as $asset)
                            <div class="col-sm-4">
                                @if ($type != 'currencies')
                                    @if (isset($asset['asset']->imageUrl))
                                        <img class="small-icon" src="{{ $asset['asset']->imageUrl }}">
                                    @endif
                                    {!! $asset['asset']->displayName !!} x{!! $asset['quantity'] !!}
                                @else
                                    {!! $asset['asset']->display('') !!}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
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
