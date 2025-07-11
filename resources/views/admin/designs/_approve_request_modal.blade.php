<p>This will accept the approval request, creating an update for the character and consuming the items and/or currency attached to this request. You will not be able to edit the traits for the character, so if those require any corrections, please
    cancel the request and ask the user to make changes.</p>
{!! Form::open(['url' => 'admin/designs/edit/' . $request->id . '/approve']) !!}
<h3>Basic Information</h3>
<div class="form-group">
    {!! Form::label('Character Category') !!}
    <select name="character_category_id" id="category" class="form-control" placeholder="Select Category">
        <option value="" data-code="">Select Category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" data-code="{{ $category->code }}" {{ $request->character->character_category_id == $category->id ? 'selected' : '' }}>{{ $category->name }} ({{ $category->code }})</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    {!! Form::label('Number') !!} {!! add_help('This number helps to identify the character and should preferably be unique either within the category, or among all characters.') !!}
    <div class="d-flex">
        {!! Form::text('number', $request->character->number, ['class' => 'form-control mr-2', 'id' => 'number']) !!}
        <a href="#" id="pull-number" class="btn btn-primary" data-toggle="tooltip"
            title="This will find the highest number assigned to a character currently and add 1 to it. It can be adjusted to pull the highest number in the category or the highest overall number - this setting is in the code.">Pull Next Number</a>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Character Code') !!} {!! add_help('This code identifies the character itself. You don\'t have to use the automatically generated code, but this must be unique among all characters (as it\'s used to generate the character\'s page URL).') !!}
    {!! Form::text('slug', $request->character->slug, ['class' => 'form-control', 'id' => 'code']) !!}
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!} {!! add_help('This section is for making additional notes about the character and is separate from the character\'s profile (this is not editable by the user).') !!}
    {!! Form::textarea('description', $request->character->description, ['class' => 'form-control wysiwyg']) !!}
</div>


<h3>Transfer Information</h3>

<div class="alert alert-info">
    These are displayed on the character's profile, but don't have any effect on site functionality except for the following:
    <ul>
        <li>If all switches are off, the character cannot be transferred by the user (directly or through trades).</li>
        <li>If a transfer cooldown is set, the character also cannot be transferred by the user (directly or through trades) until the cooldown is up.</li>
    </ul>
</div>
<div class="form-group">
    {!! Form::checkbox('is_giftable', 1, $request->character->is_giftable, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_giftable', 'Is Giftable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_tradeable', 1, $request->character->is_tradeable, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_tradeable', 'Is Tradeable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_sellable', 1, $request->character->is_sellable, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'resellable']) !!}
    {!! Form::label('is_sellable', 'Is Resellable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="card mb-3" id="resellOptions">
    <div class="card-body">
        {!! Form::label('Resale Value') !!} {!! add_help('This value is publicly displayed on the character\'s page.') !!}
        {!! Form::text('sale_value', $request->character->sale_value, ['class' => 'form-control']) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('On Transfer Cooldown Until (Optional)') !!}
    {!! Form::text('transferrable_at', $request->character->transferrable_at, ['class' => 'form-control datepicker']) !!}
</div>

<h3>Image Settings</h3>

<div class="form-group">
    {!! Form::checkbox('set_active', 1, true, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('set_active', 'Set Active Image', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will set the new approved image as the character\'s masterlist image.') !!}
</div>
<div class="form-group">
    {!! Form::checkbox('invalidate_old', 1, true, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('invalidate_old', 'Invalidate Old Image', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will mark the last image attached to the character as an invalid reference.') !!}
</div>
@if (config('lorekeeper.extensions.remove_myo_image') && $request->character->is_myo_slot)
    <div class="form-group">
        {!! Form::label('Remove MYO Image') !!} {!! add_help('This will either hide or delete the MYO slot placeholder image if set.') !!}
        {!! Form::select('remove_myo_image', [0 => 'Leave MYO Image', 1 => 'Hide MYO Image', 2 => 'Delete MYO Image'], null, ['class' => 'form-control']) !!}
    </div>
@endif

<div class="text-right">
    {!! Form::submit('Approve Request', ['class' => 'btn btn-success']) !!}
</div>
{!! Form::close() !!}

@include('widgets._character_create_options_js')
@include('widgets._character_code_js')
@include('widgets._datetimepicker_js')
@include('js._tinymce_wysiwyg')
