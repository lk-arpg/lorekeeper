@if ($queue)
    {!! Form::open(['url' => 'admin/data/queues/delete/' . $queue->id]) !!}

    <p>You are about to delete the queue <strong>{{ $queue->name }}</strong>. This is not reversible. If submissions exist under this queue, you will not be able to delete it.</p>
    <p>Are you sure you want to delete <strong>{{ $queue->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Queue', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid queue selected.
@endif
