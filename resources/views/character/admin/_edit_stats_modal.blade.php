{!! Form::open(['url' => $isMyo ? 'admin/myo/' . $character->id . '/stats' : 'admin/character/' . $character->slug . '/stats']) !!}
@if ($isMyo)
    <div class="mb-3">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $character->name, ['class' => 'form-control']) !!}
    </div>
@else
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                {!! Form::label('Character Category') !!}
                {!! Form::select('character_category_id', $categories, $character->category->id, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                {!! Form::label('Number') !!} {!! add_help('This number helps to identify the character and should preferably be unique either within the category, or among all characters.') !!}
                {!! Form::text('number', $number, ['class' => 'form-control me-2', 'id' => 'number']) !!}
            </div>
        </div>
    </div>
    <div class="mb-3">
        {!! Form::label('Character Code') !!} {!! add_help('This code identifies the character itself. This must be unique among all characters (as it\'s used to generate the character\'s page URL).') !!}
        {!! Form::text('slug', $character->slug, ['class' => 'form-control', 'id' => 'code']) !!}
    </div>
@endif

<div class="alert alert-info">
    These are displayed on the character's profile, but don't have any effect on site functionality except for the following:
    <ul>
        <li>If all switches are off, the character cannot be transferred by the user (directly or through trades).</li>
        <li>If a transfer cooldown is set, the character also cannot be transferred by the user (directly or through trades) until the cooldown is up.</li>
    </ul>
</div>
<div class="mb-3">
    {!! Form::checkbox('is_giftable', 1, $character->is_giftable, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_giftable', 'Is Giftable', ['class' => 'form-check-label ms-3']) !!}
</div>
<div class="mb-3">
    {!! Form::checkbox('is_tradeable', 1, $character->is_tradeable, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_tradeable', 'Is Tradeable', ['class' => 'form-check-label ms-3']) !!}
</div>
<div class="mb-3">
    {!! Form::checkbox('is_sellable', 1, $character->is_sellable, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'resellable']) !!}
    {!! Form::label('is_sellable', 'Is Resellable', ['class' => 'form-check-label ms-3']) !!}
</div>
<div class="mb-3">
    {!! Form::label('Resale Value') !!} {!! add_help('This value is publicly displayed on the character\'s page. It\'s hidden if zero or lower.') !!}
    {!! Form::text('sale_value', $character->sale_value, ['class' => 'form-control']) !!}
</div>
<div class="mb-3">
    {!! Form::label('On Transfer Cooldown Until (Optional)') !!}
    <div class="input-group">
        {!! Form::text('transferrable_at', $character->transferrable_at, ['class' => 'form-control datepickeralt']) !!}
        <div class="input-group-append">
            <a class="btn btn-info collapsed" href="#collapsedt" data-bs-toggle="collapse"><i class="fas fa-calendar-alt"></i></a>
        </div>
    </div>
    <div class="collapse datepicker" id="collapsedt"></div>
</div>

<div class="text-end">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

@include('widgets._datetimepicker_js', ['dtinline' => 'datepickeralt', 'dtvalue' => $character->transferrable_at])
