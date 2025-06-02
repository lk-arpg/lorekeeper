@if (!$raffle->id)
    <p>
        Enter basic information about this raffle. Tickets can be added after the raffle is created.
    </p>
@endif
{!! Form::open(['url' => 'admin/raffles/edit/raffle/' . ($raffle->id ?: '')]) !!}
<div class="mb-3">
    {!! Form::label('name', 'Raffle Name') !!} {!! add_help('This is the name of the raffle. Naming it something after what is being raffled is suggested (does not have to be unique).') !!}
    {!! Form::text('name', $raffle->name, ['class' => 'form-control']) !!}
</div>
<div class="mb-3">
    {!! Form::label('winner_count', 'Number of Winners to Draw') !!}
    {!! Form::text('winner_count', $raffle->winner_count, ['class' => 'form-control']) !!}
</div>
<div class="mb-3">
    {!! Form::label('group_id', 'Raffle Group') !!} {!! add_help('Raffle groups must be created before you can select them here.') !!}
    {!! Form::select('group_id', $groups, $raffle->group_id, ['class' => 'form-control']) !!}
</div>
<div class="mb-3">
    {!! Form::label('order', 'Raffle Order') !!} {!! add_help('Enter a number. If a group of raffles is rolled, raffles will be drawn in ascending order.') !!}
    {!! Form::text('order', $raffle->order ?: 0, ['class' => 'form-control']) !!}
</div>
<div class="mb-3">
    {!! Form::label('ticket_cap', 'Ticket Cap (Optional)') !!} {!! add_help('A number of tickets per individual to cap at. Leave empty or unset to have no cap.') !!}
    {!! Form::text('ticket_cap', $raffle->ticket_cap ?: null, ['class' => 'form-control']) !!}
</div>
<div class="mb-3">
    <label class="control-label">
        {!! Form::checkbox('is_active', 1, $raffle->is_active, ['class' => 'form-check-input me-2', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_displayed', 'Active (visible to users)', ['class' => 'form-check-label ms-3']) !!}
    </label>
</div>
<div class="text-end">
    {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}
