{!! Form::open(['url' => 'admin/raffles/edit/reroll/' . $ticket->id]) !!}

<p>Are you sure you want to reroll ticket #{{ $ticket->id }}, {!! $ticket->displayHolderName !!}?</p>

{!! Form::text('reason', null, ['placeholder' => 'Reason for reroll (Required)', 'class' => 'form-control']) !!}

<div class="text-right mt-2">
    {!! Form::submit('Yes, reroll this winner', ['class' => 'btn btn-danger']) !!}
</div>

{!! Form::close() !!}
