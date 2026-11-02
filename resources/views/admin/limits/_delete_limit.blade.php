@if ($limit)
    {!! Form::open(['url' => 'admin/data/limits/delete/' . $limit->id]) !!}

    <p>You are about to delete the limit <strong>{{ $limit->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $limit->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Limit', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid limit selected.
@endif
