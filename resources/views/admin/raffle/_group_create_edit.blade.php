@if (!$group->id)
    <p>
        Raffle groups allow you to create sets of raffles that can be rolled in sequence. Users that have won something in a previous raffle will be removed from later raffles (so users will not be able to win more than once in a group).
    </p>
@endif
{!! Form::open(['url' => 'admin/raffles/edit/group/' . ($group->id ?: '')]) !!}
<div class="mb-3">
    {!! Form::label('name', 'Group Name') !!} {!! add_help('This is the name of the raffle group (does not have to be unique), e.g. July Monthly Raffles, Event Guest Sales') !!}
    {!! Form::text('name', $group->name, ['class' => 'form-control']) !!}
</div>
<div class="mb-3">
    <label class="control-label">
        {!! Form::checkbox('is_active', 1, $group->is_active, ['class' => 'form-check-input me-2', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Active (visible to users)', ['class' => 'form-check-label ms-3']) !!} {!! add_help('This setting will cascade to any raffles in this group (setting the group to active will set the raffles within it to active and vice versa). Not retroactive.') !!}
    </label>
</div>
<div class="text-end">
    {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}
