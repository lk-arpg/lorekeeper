{!! Form::open(['url' => 'admin/masterlist/transfer/' . $transfer->id]) !!}
@if ($transfer->status == 'Accepted')
    <p>This will process the transfer of {!! $transfer->character->displayName !!} from {!! $transfer->sender->displayName !!} to {!! $transfer->recipient->displayName !!} immediately.</p>
@else
    <p>This will approve the transfer of {!! $transfer->character->displayName !!} from {!! $transfer->sender->displayName !!} to {!! $transfer->recipient->displayName !!}, and it will be processed once the recipient accepts it.</p>
@endif
<div class="mb-3">
    {!! Form::label('cooldown', 'Cooldown (days)') !!}
    {!! Form::text('cooldown', $cooldown, ['class' => 'form-control']) !!}
</div>
<div class="text-end">
    {!! Form::submit('Approve', ['class' => 'btn btn-success', 'name' => 'action']) !!}
</div>
{!! Form::close() !!}
