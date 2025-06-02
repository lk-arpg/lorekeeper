@if ($subtype)
    {!! Form::open(['url' => 'admin/data/subtypes/delete/' . $subtype->id]) !!}

    <p>You are about to delete the subtype <strong>{{ $subtype->name }}</strong>. This is not reversible. If traits and/or characters that have this subtype exist, you will not be able to delete this subtype.</p>
    <p>Are you sure you want to delete <strong>{{ $subtype->name }}</strong>?</p>

    <div class="text-end">
        {!! Form::submit('Delete Subtype', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid subtype selected.
@endif
