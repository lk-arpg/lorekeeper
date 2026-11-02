@extends('character.design.layout')

@section('design-title')
    Request (#{{ $request->id }}) :: Add-ons
@endsection

@section('design-content')
    {!! breadcrumbs(['Design Approvals' => 'designs', 'Request (#' . $request->id . ')' => 'designs/' . $request->id, 'Add-ons' => 'designs/' . $request->id . '/addons']) !!}

    @include('character.design._header', ['request' => $request])

    <h2>Add-ons</h2>

    @if ($request->status == 'Draft' && $request->user_id == Auth::user()->id && $request->character)
        <p>Select items and/or currency to add onto your request. These items will be removed from your inventory{{ $request->character->is_myo_slot ? '' : ' and/or character' }} but refunded if removed from the request, the request is rejected, or the
            request is deleted. If you don't intend to attach any items/currency, click the Save button once to mark this section complete regardless.</p>
        {!! Form::open(['url' => 'designs/' . $request->id . '/addons']) !!}
        @include('widgets._inventory_select', ['user' => Auth::user(), 'inventory' => $inventory, 'categories' => $categories, 'selected' => $request->inventory])
        @include('widgets._bank_select', ['owner' => Auth::user(), 'selected' => $request->userBank])

        @if (!$request->character->is_myo_slot)
            @include('widgets._bank_select', ['owner' => $request->character, 'selected' => $request->characterBank])
        @endif

        <div class="text-right">
            {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @else
        <p>Items and/or currency listed have been removed from their holders and will be refunded if the request is rejected.</p>
        @if ($inventory && count($inventory))
            <h3>{!! $request->user->displayName !!}'s Inventory</h3>
            <div class="card mb-3">
                <div class="card-body">
                    <table class="table table-sm">
                        <thead class="thead-light">
                            <tr class="d-flex">
                                <th class="col-2">Item</th>
                                <th class="col-4">Source</th>
                                <th class="col-4">Notes</th>
                                <th class="col-2">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inventory['user_items'] as $itemRow)
                                <tr class="d-flex">
                                    <td class="col-2">
                                        @if (isset($itemRow['asset']) && isset($items[$itemRow['asset']->item_id]->image_url))
                                            <img class="small-icon" src="{{ $items[$itemRow['asset']->item_id]->image_url }}" alt=" {{ $items[$itemRow['asset']->item_id]->name }} ">
                                        @endif {!! isset($itemRow['asset']) ? $items[$itemRow['asset']->item_id]->name : '<i>Deleted User Item</i>' !!}
                                    <td class="col-4">{!! isset($itemRow['asset']) && array_key_exists('data', $itemRow['asset']->data) ? ($itemRow['asset']->data['data'] ? $itemRow['asset']->data['data'] : 'N/A') : 'N/A' !!}</td>
                                    <td class="col-4">{!! isset($itemRow['asset']) && array_key_exists('notes', $itemRow['asset']->data) ? ($itemRow['asset']->data['notes'] ? $itemRow['asset']->data['notes'] : 'N/A') : 'N/A' !!}</td>
                                    <td class="col-2">{!! $itemRow['quantity'] !!}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        @if (count($request->userBank))
            <h3>{!! $request->user->displayName !!}'s Bank</h3>
            <table class="table table-sm mb-3">
                <thead>
                    <tr>
                        <th width="70%">Currency</th>
                        <th width="30%">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($request->getBank('user') as $currency)
                        <tr>
                            <td>{!! $currency->displayName !!}</td>
                            <td>{{ $currency->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        @if ($request->character && count($request->characterBank))
            <h3>{!! $request->character->displayName !!}'s Bank</h3>
            <table class="table table-sm mb-3">
                <thead>
                    <tr>
                        <th width="70%">Currency</th>
                        <th width="30%">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($request->getBank('character') as $currency)
                        <tr>
                            <td>{!! $currency->displayName !!}</td>
                            <td>{{ $currency->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

@endsection

@section('scripts')
    @include('widgets._bank_select_row', ['owners' => [Auth::user(), $request->character]])
    @include('widgets._inventory_select_js')
    @include('widgets._bank_select_js', [])
@endsection
