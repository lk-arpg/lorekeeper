<h1>MYO Slot Settings</h1>

<h3>Basic Information</h3>
<div class="form-group">
    {!! Form::label('Name') !!} {!! add_help('Enter a descriptive name for the type of character this slot can create, e.g. Rare MYO Slot. This will be listed on the MYO slot masterlist.') !!}
    {!! Form::text('name', $tag->getData()['name'], ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    @if ($isMyo)
        {!! add_help('This section is for making additional notes about the MYO slot. If there are restrictions for the character that can be created by this slot that cannot be expressed with the options below, use this section to describe them.') !!}
    @else
        {!! add_help('This section is for making additional notes about the character and is separate from the character\'s profile (this is not editable by the user).') !!}
    @endif
    {!! Form::textarea('description', $tag->getData()['description'], ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_visible', 1, $tag->getData()['is_visible'], ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
        'Turn this off to hide the ' . ($isMyo ? 'MYO slot' : 'character') . '. Only mods with the Manage Masterlist power (that\'s you!) can view it - the owner will also not be able to see the ' . ($isMyo ? 'MYO slot' : 'character') . '\'s page.',
    ) !!}
</div>

<h3>Transfer Information</h3>

<div class="alert alert-info">
    These are displayed on the MYO slot's profile, but don't have any effect on site functionality except for the following:
    <ul>
        <li>If all switches are off, the MYO slot cannot be transferred by the user (directly or through trades).</li>
        <li>If a transfer cooldown is set, the MYO slot also cannot be transferred by the user (directly or through trades) until the cooldown is up.</li>
    </ul>
</div>
<div class="form-group">
    {!! Form::checkbox('is_giftable', 1, $tag->getData()['is_giftable'], ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_giftable', 'Is Giftable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_tradeable', 1, $tag->getData()['is_tradeable'], ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_tradeable', 'Is Tradeable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_sellable', 1, $tag->getData()['is_sellable'], ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'resellable']) !!}
    {!! Form::label('is_sellable', 'Is Resellable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="card mb-3" id="resellOptions">
    <div class="card-body">
        {!! Form::label('Resale Value') !!} {!! add_help('This value is publicly displayed on the MYO slot\'s page.') !!}
        {!! Form::text('sale_value', $tag->getData()['sale_value'], ['class' => 'form-control']) !!}
    </div>
</div>

<h3>Traits</h3>

<div class="form-group">
    {!! Form::label('Species') !!} {!! add_help('This will lock the slot into a particular species. Leave it blank if you would like to give the user a choice.') !!}
    {!! Form::select('species_id', $specieses, $tag->getData()['species_id'], ['class' => 'form-control', 'id' => 'species']) !!}
</div>

<div class="form-group" id="subtypes">
    {!! Form::label('Subtypes (Optional)') !!} {!! add_help(
        'This will lock the slot into a particular subtype. Leave it blank if you would like to give the user a choice, or not select a subtype. The subtype must match the species selected above, and if no species is specified, the subtype will not be applied.',
    ) !!}
    {!! Form::select('subtype_ids[]', $subtypes, $tag->getData()['species_id'] ? $tag->getData()['subtype_ids'] : null, ['class' => 'form-control', 'placeholder' => 'Select Species First', 'id' => 'subtype', 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('Character Rarity') !!} {!! add_help('This will lock the slot into a particular rarity. Leave it blank if you would like to give the user more choices.') !!}
    {!! Form::select('rarity_id', $rarities, $tag->getData()['rarity_id'], ['class' => 'form-control']) !!}
</div>

@section('scripts')
    @parent
    @include('widgets._character_create_options_js')
    @include('js._tinymce_wysiwyg')
    <script>
        $("#species").change(function() {
            var species = $('#species').val();
            var myo = '<?php echo $isMyo; ?>';
            $.ajax({
                type: "GET",
                url: "{{ url('admin/masterlist/check-subtype') }}?species=" + species + "&myo=" + myo,
                dataType: "text"
            }).done(function(res) {
                $("#subtypes").html(res);
                $("#subtype").selectize({
                    maxItems: {{ config('lorekeeper.extensions.multiple_subtype_limit') }},
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        });

        $('#subtype').selectize({
            maxItems: {{ config('lorekeeper.extensions.multiple_subtype_limit') }},
        });
    </script>
@endsection
