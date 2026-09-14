@if ($criterion)
    {!! Form::open(['url' => 'admin/data/criteria/' . (isset($path) ? $path : '') . 'delete/' . $criterion->id]) !!}

    <p>You are about to delete the {{ $name }} <strong>"{{ $criterion->name }}"</strong>. This is not reversible. Any nested structures will also be deleted.</p>
    <p>Are you sure you want to delete <strong>"{{ $criterion->name }}"</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete ' . $name, ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ $name }} selected.
@endif
