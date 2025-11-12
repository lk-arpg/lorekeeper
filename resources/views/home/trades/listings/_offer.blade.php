<h3>Offering:</h3>

<div class="card mb-3 trade-offer">
    @if (isset($data) && $data['user_items'])
        <div class="card-header h5">
            Items
        </div>
        <div class="card-body user-items">
            <table class="table table-sm">
                <thead class="thead-light">
                    <tr class="d-flex">
                        <th class="col-3">Item</th>
                        <th class="col-4">Source</th>
                        <th class="col-3">Notes</th>
                        <th class="col-2">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['user_items'] as $itemRow)
                        <tr class="d-flex">
                            <td class="col-3">
                                @if (isset($items[$itemRow['asset']->item_id]->image_url))
                                    <img class="small-icon" src="{{ $items[$itemRow['asset']->item_id]->image_url }}">
                                @endif <a href="/world/items?name={{ $items[$itemRow['asset']->item_id]->name }}">{!! $items[$itemRow['asset']->item_id]->name !!}</a>
                            <td class="col-4">{!! array_key_exists('data', $itemRow['asset']->data) ? ($itemRow['asset']->data['data'] ? $itemRow['asset']->data['data'] : 'N/A') : 'N/A' !!}</td>
                            <td class="col-3">{!! array_key_exists('notes', $itemRow['asset']->data) ? ($itemRow['asset']->data['notes'] ? $itemRow['asset']->data['notes'] : 'N/A') : 'N/A' !!}</td>
                            <td class="col-2">{!! $itemRow['quantity'] !!}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if (isset($data) && $data['characters'])
        <div class="card-header {{ isset($data['user_items']) ? 'border-top' : '' }} border-bottom-0 h5">
            Characters
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($data['characters'] as $character)
                    <div class="col-lg-2 col-sm-3 col-6 mb-3">
                        <div class="text-center inventory-item">
                            <div class="mb-1">
                                <a class="inventory-stack"><img src="{{ $character['asset']->image->thumbnailUrl }}" class="img-thumbnail" /></a>
                            </div>
                            <div>
                                <a class="inventory-stack inventory-stack-name">{!! $character['asset']->displayName !!}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if (isset($data) && $data['currencies'])
        <div class="card-header {{ false ? 'border-top' : '' }} border-bottom-0 h5">
            Currencies
        </div>
        <ul class="list-group list-group-flush">
            @foreach ($data['currencies'] as $currency)
                <li class="list-group-item border-bottom-0 border-top currency-item">
                    {!! $currency['asset']->display('') !!}
                </li>
            @endforeach
        </ul>
    @endif
    @if (isset($listing->data['offering_etc']) && $listing->data['offering_etc'])
        <div class="card-header {{ isset($data) ? 'border-top' : '' }} h5">
            Other Goods & Services
        </div>
        <ul class="list-group list-group-flush">
            <div class="card-body">
                {!! nl2br(htmlentities($listing->data['offering_etc'])) !!}
            </div>
        </ul>
    @endif
    @if (!isset($listing->data['offering']) && !isset($listing->data['offering_etc']))
        <div class="card-body">{!! $user->displayName !!} has not offered anything.</div>
    @endif
</div>
