<h3>Seeking:</h3>

<div class="card mb-3 trade-offer">
    @if (isset($data))
        @foreach ($data as $type => $assets)
            @if ($assets)
                <div class="card-header h5">
                    {!! ucfirst($type) !!}
                </div>
                <div class="card-body user-items">
                    <table class="table table-sm">
                        <thead class="thead-light">
                            <tr class="d-flex">
                                <th class="col">Type</th>
                                <th class="col">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assets as $asset)
                                <tr class="d-flex">
                                    <td class="col-6">
                                        @if ($asset['asset']->imageUrl || $asset['asset']->currencyIconUrl)
                                            <img class="small-icon" src="{{ $asset['asset']->imageUrl ?? $asset['asset']->currencyIconUrl }}" alt="{{ $asset['asset']->displayName }}">
                                        @endif
                                        {!! $asset['asset']->displayName !!}
                                    </td>
                                    <td class="col-6">{!! $asset['quantity'] !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
    @endif
    @if (isset($listing->data['seeking_etc']) && $listing->data['seeking_etc'])
        <div class="card-header {{ isset($data) ? 'border-top' : '' }} h5">
            Other Goods & Services
        </div>
        <ul class="list-group list-group-flush">
            <div class="card-body">
                {!! nl2br(htmlentities($listing->data['seeking_etc'])) !!}
            </div>
        </ul>
    @endif
    @if (!isset($listing->data['seeking']) && !isset($listing->data['seeking_etc']))
        <div class="card-body">
            {!! $listing->user->displayName !!} is not seeking anything.
        </div>
    @endif
</div>
