@if ($stock)
    {!! Form::open(['url' => 'admin/data/shops/stock/delete/' . $stock->id]) !!}

    <p>You are about to delete the stock <strong>{{ $stock->item?->name ?? 'deleted ' . $stock->stock_type }}</strong>.</p>
    <p>Are you sure you want to delete <strong>{{ $stock->item->name ?? 'deleted ' . $stock->stock_type }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Stock', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid stock selected.
@endif
