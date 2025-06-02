@if ($sublist)
    {!! Form::open(['url' => 'admin/data/sublists/delete/' . $sublist->id]) !!}

    <p>You are about to delete the sublist <strong>{{ $sublist->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $sublist->name }}</strong>?</p>

    <div class="text-end">
        {!! Form::submit('Delete Sublist', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid sublist selected.
@endif
