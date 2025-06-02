@if ($trade && $trade->status == 'Open')
    <p>This will cancel the entire trade and return all attachments to their owners. Are you sure you want to do this?</p>
    {!! Form::open(['url' => 'trades/' . $trade->id . '/cancel-trade']) !!}
    <div class="text-end">
        {!! Form::submit('Cancel', ['class' => 'btn btn-danger']) !!}
    </div>
    {!! Form::close() !!}
@else
    <p>Invalid trade selected.</p>
@endif
