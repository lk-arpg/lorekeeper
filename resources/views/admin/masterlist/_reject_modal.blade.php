{!! Form::open(['url' => 'admin/masterlist/transfer/' . $transfer->id]) !!}
<p>This will reject the transfer of {!! $transfer->character->displayName !!} from {!! $transfer->sender->displayName !!} to {!! $transfer->recipient->displayName !!} automatically. The transfer cooldown will not be applied. Are you sure?</p>
<div class="form-group">
    {!! Form::label('reason', 'Reason for Rejection (optional)') !!}
    {!! Form::textarea('reason', '', ['class' => 'form-control']) !!}
</div>
<div class="text-right">
    {!! Form::submit('Reject', ['class' => 'btn btn-danger', 'name' => 'action', 'value' => 'reject']) !!}
</div>
{!! Form::close() !!}
