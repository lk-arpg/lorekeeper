@if (!$raffle->id)
    <p>
        Enter basic information about this raffle. Tickets and rewards can be added after the raffle is created.
    </p>
@endif
{!! Form::open(['url' => 'admin/raffles/edit/raffle/' . ($raffle->id ?: '')]) !!}
<div class="form-group">
    {!! Form::label('name', 'Raffle Name') !!} {!! add_help('This is the name of the raffle. Naming it something after what is being raffled is suggested (does not have to be unique).') !!}
    {!! Form::text('name', $raffle->name, ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::label('Description (Optional)') !!} {!! add_help('This is a full description of the raffle that shows up on the raffle page.') !!}
    {!! Form::textarea('description', $raffle->description, ['class' => 'form-control wysiwyg']) !!}
</div>
<div class="form-group">
    {!! Form::label('winner_count', 'Number of Winners to Draw') !!}
    {!! Form::text('winner_count', $raffle->winner_count ?? 1, ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::label('group_id', 'Raffle Group') !!} {!! add_help('Raffle groups must be created before you can select them here.') !!}
    {!! Form::select('group_id', $groups, $raffle->group_id, ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::label('order', 'Raffle Order') !!} {!! add_help('Enter a number. If a group of raffles is rolled, raffles will be drawn in ascending order.') !!}
    {!! Form::text('order', $raffle->order ?: 0, ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::label('ticket_cap', 'Ticket Cap (Optional)') !!} {!! add_help('A number of tickets per individual to cap at. Leave empty or unset to have no cap.') !!}
    {!! Form::text('ticket_cap', $raffle->ticket_cap ?: null, ['class' => 'form-control']) !!}
</div>
<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::checkbox('is_active', 1, $raffle->is_active, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Active (visible to users)', ['class' => 'form-check-label ml-3']) !!}
    </div>
    <div class="col-md-6 form-group">
        {!! Form::checkbox('allow_entry', 1, $raffle->allow_entry, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('allow_entry', 'Allow users to enter?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Allows users to enter themselves into the raffle.') !!}
    </div>
    <div class="col-md-6 form-group">
        {!! Form::checkbox('is_fto', 1, $raffle->is_fto, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_fto', 'FTO / Non-Owner Only?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Only users that are Non-Owners or are FTO can enter if turned on.') !!}
    </div>
    <div class="col-md-6 form-group">
        {!! Form::checkbox('unordered', 1, $raffle->unordered, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('unordered', 'Unordered Results?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If there are less entrants than winners, the numbers will be random instead of 1 - X.') !!}
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('end_at', 'End Time (Optional)') !!} {!! add_help('Prompts cannot be submitted to the queue after the ending time.') !!}
            {!! Form::text('end_at', $raffle->end_at, ['class' => 'form-control datepicker']) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('roll_on_end', 'Roll On End?') !!} {!! add_help('When the end time comes about, do you wish the raffle to roll itself?') !!}
            {!! Form::checkbox('roll_on_end', 1, $raffle->roll_on_end, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
        </div>
    </div>
</div>

{{-- blade-formatter-disable --}}
@include('widgets._add_rewards', [
    'title' => 'Entry Rewards',
    'object' => $raffle,
    'useForm' => false,
    'showLootTables' => true,
    'showCharacters' => true,
    'prefix' => 'entry_',
    'loots' => hasRewards($raffle) ? getRewards($raffle, true)->where('data->type', 'entry_reward')->get() : null,
    'info' => 'If you want users to receive a reward for entering, add it here. This only applies to users with an account, who are entered by an admin or who self entered.<br><strong>Users entered as an alias are not eligible to receive rewards at this time.</strong>',
])
@include('widgets._add_rewards', [
    'title' => 'Winner Rewards',
    'object' => $raffle,
    'useForm' => false,
    'showLootTables' => true,
    'showCharacters' => true,
    'prefix' => 'winner_',
    'loots' => hasRewards($raffle) ? getRewards($raffle, true)->where('data->type', 'winner_reward')->get() : null,
    'info' => 'The winner rewards are what users receive if they win the raffle. If you want to give a reward to the winner(s) of the raffle, add it here.<br/><strong>Users entered as an alias are not eligible to receive rewards at this time.</strong>' .
        '<div class="alert alert-warning mt-3">To award to all winners, leave the position blank.</div>',
    'extra_fields' => [
        'position' => [
            'type' => 'number',
            'label' => 'Winner Position (Optional)',
            'tooltip' => 'This is the position of the winner.',
            'default' => null,
            'placeholder' => 'Optional',
        ],
    ],
])
{{-- blade-formatter-enable --}}

<div class="text-right">
    {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

@include('widgets._datetimepicker_js')
@include('js._tinymce_wysiwyg')
