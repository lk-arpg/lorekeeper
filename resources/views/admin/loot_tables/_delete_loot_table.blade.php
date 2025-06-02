@if ($table)
    {!! Form::open(['url' => 'admin/data/loot-tables/delete/' . $table->id]) !!}

    <p>You are about to delete the loot table <strong>{{ $table->name }}</strong>. This is not reversible. If prompts that use this loot table exist, you will not be able to delete this table.</p>
    <p>Are you sure you want to delete <strong>{{ $table->name }}</strong>?</p>

    <div class="text-end">
        {!! Form::submit('Delete Loot Table', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid loot table selected.
@endif
