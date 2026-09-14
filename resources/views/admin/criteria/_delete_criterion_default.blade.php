@if ($default)
    {!! Form::open(['url' => 'admin/data/criteria-defaults/' . (isset($path) ? $path : '') . 'delete/' . $default->id]) !!}

    <p>You are about to delete the {{ $name }} <strong>"{{ $default->name }}"</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>"{{ $default->name }}"</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete ' . $name, ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ $name }} selected.
@endif
