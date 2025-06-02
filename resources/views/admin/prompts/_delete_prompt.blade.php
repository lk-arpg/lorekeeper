@if ($prompt)
    {!! Form::open(['url' => 'admin/data/prompts/delete/' . $prompt->id]) !!}

    <p>You are about to delete the prompt <strong>{{ $prompt->name }}</strong>. This is not reversible. If submissions exist under this prompt, you will not be able to delete it.</p>
    <p>Are you sure you want to delete <strong>{{ $prompt->name }}</strong>?</p>

    <div class="text-end">
        {!! Form::submit('Delete Prompt', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid prompt selected.
@endif
