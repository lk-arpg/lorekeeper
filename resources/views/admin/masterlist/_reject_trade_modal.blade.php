{!! Form::open(['url' => 'admin/masterlist/trade/' . $trade->id]) !!}
<p>This will reject the trade between {!! $trade->sender->displayName !!} and {!! $trade->recipient->displayName !!} automatically, returning all items/currency/characters to their owners. The character transfer cooldown will not be applied. Are you sure?</p>
<div class="mb-3">
    {!! Form::label('reason', 'Reason for Rejection (optional)') !!}
    {!! Form::textarea('reason', '', ['class' => 'form-control']) !!}
</div>
<div class="text-end">
    {!! Form::submit('Reject', ['class' => 'btn btn-danger', 'name' => 'action']) !!}
</div>
{!! Form::close() !!}
