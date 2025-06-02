@if ($currency)
    {!! Form::open(['url' => 'admin/data/currencies/delete/' . $currency->id]) !!}

    <p>You are about to delete the currency <strong>{{ $currency->name }}</strong>. This is not reversible. If users who possess this currency exist, their owned currency will also be deleted.</p>
    <p>Are you sure you want to delete <strong>{{ $currency->name }}</strong>?</p>

    <div class="text-end">
        {!! Form::submit('Delete Currency', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid currency selected.
@endif
