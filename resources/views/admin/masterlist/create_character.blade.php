@extends('admin.layout')

@section('admin-title')
    Create {{ $isMyo ? 'MYO Slot' : 'Character' }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Create ' . ($isMyo ? 'MYO Slot' : 'Character') => 'admin/masterlist/create-' . ($isMyo ? 'myo' : 'character')]) !!}

    <h1>Create {{ $isMyo ? 'MYO Slot' : 'Character' }}</h1>

    @if (!$isMyo && !count($categories))

        <div class="alert alert-danger">Creating characters requires at least one <a href="{{ url('admin/data/character-categories') }}">character category</a> to be created first, as character categories are used to generate the character code.</div>
    @else
        {!! Form::open(['url' => 'admin/masterlist/create-' . ($isMyo ? 'myo' : 'character'), 'files' => true]) !!}

        <h3>Basic Information</h3>

        @if ($isMyo)
            <div class="form-group">
                {!! Form::label('Name') !!} {!! add_help('Enter a descriptive name for the type of character this slot can create, e.g. Rare MYO Slot. This will be listed on the MYO slot masterlist.') !!}
                {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
            </div>
        @endif

        <div class="alert alert-info">
            Fill in either of the owner fields - you can select a user from the list if they have registered for the site, or enter the URL of their off-site profile, such as their deviantArt profile, if they don't have an account. If the owner registers
            an account later and links their account, {{ $isMyo ? 'MYO slot' : 'character' }}s linked to that account's profile will automatically be credited to their site account. If both fields are filled, the URL field will be ignored.
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                {!! Form::label('Owner') !!}
                {!! Form::select('user_id', $userOptions, old('user_id'), ['class' => 'form-control', 'placeholder' => 'Select User', 'id' => 'userSelect']) !!}
            </div>
            <div class="col-md-6 form-group">
                {!! Form::label('Owner URL (Optional)') !!}
                {!! Form::text('owner_url', old('owner_url'), ['class' => 'form-control']) !!}
            </div>
        </div>

        @if (!$isMyo)
            <div class="row">
                <div class="col-md-6 form-group">
                    {!! Form::label('Character Category') !!}
                    <select name="character_category_id" id="category" class="form-control" placeholder="Select Category">
                        <option value="" data-code="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" data-code="{{ $category->code }}" {{ old('character_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }} ({{ $category->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    {!! Form::label('Number') !!} {!! add_help('This number helps to identify the character and should preferably be unique either within the category, or among all characters.') !!}
                    <div class="d-flex">
                        {!! Form::text('number', old('number'), ['class' => 'form-control mr-2', 'id' => 'number']) !!}
                        <a href="#" id="pull-number" class="btn btn-primary" data-toggle="tooltip"
                            title="This will find the highest number assigned to a character currently and add 1 to it. It can be adjusted to pull the highest number in the category or the highest overall number - this setting is in the code.">Pull
                            Next Number</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-{{ config('lorekeeper.settings.enable_character_content_warnings') ? 6 : 12 }}">
                    <div class="form-group">
                        {!! Form::label('Character Code') !!} {!! add_help('This code identifies the character itself. You don\'t have to use the automatically generated code, but this must be unique among all characters (as it\'s used to generate the character\'s page URL).') !!}
                        {!! Form::text('slug', old('slug'), ['class' => 'form-control', 'id' => 'code']) !!}
                    </div>
                </div>
                @if (config('lorekeeper.settings.enable_character_content_warnings'))
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('Content Warnings') !!} {!! add_help('These warnings will be displayed on the character\'s page. They are not required, but are recommended if the character contains sensitive content.') !!}
                            {!! Form::text('content_warnings', old('content_warnings'), ['class' => 'form-control', 'id' => 'warningList']) !!}
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="form-group">
            {!! Form::label('Description (Optional)') !!}
            @if ($isMyo)
                {!! add_help('This section is for making additional notes about the MYO slot. If there are restrictions for the character that can be created by this slot that cannot be expressed with the options below, use this section to describe them.') !!}
            @else
                {!! add_help('This section is for making additional notes about the character and is separate from the character\'s profile (this is not editable by the user).') !!}
            @endif
            {!! Form::textarea('description', old('description'), ['class' => 'form-control wysiwyg']) !!}
        </div>

        <div class="form-group">
            {!! Form::checkbox('is_visible', 1, old('is_visible'), ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
                'Turn this off to hide the ' . ($isMyo ? 'MYO slot' : 'character') . '. Only mods with the Manage Masterlist power (that\'s you!) can view it - the owner will also not be able to see the ' . ($isMyo ? 'MYO slot' : 'character') . '\'s page.',
            ) !!}
        </div>

        <h3>Transfer Information</h3>

        <div class="alert alert-info">
            These are displayed on the {{ $isMyo ? 'MYO slot' : 'character' }}'s profile, but don't have any effect on site functionality except for the following:
            <ul>
                <li>If all switches are off, the {{ $isMyo ? 'MYO slot' : 'character' }} cannot be transferred by the user (directly or through trades).</li>
                <li>If a transfer cooldown is set, the {{ $isMyo ? 'MYO slot' : 'character' }} also cannot be transferred by the user (directly or through trades) until the cooldown is up.</li>
            </ul>
        </div>
        <div class="form-group">
            {!! Form::checkbox('is_giftable', 1, old('is_giftable'), ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_giftable', 'Is Giftable', ['class' => 'form-check-label ml-3']) !!}
        </div>
        <div class="form-group">
            {!! Form::checkbox('is_tradeable', 1, old('is_tradeable'), ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_tradeable', 'Is Tradeable', ['class' => 'form-check-label ml-3']) !!}
        </div>
        <div class="form-group">
            {!! Form::checkbox('is_sellable', 1, old('is_sellable'), ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'resellable']) !!}
            {!! Form::label('is_sellable', 'Is Resellable', ['class' => 'form-check-label ml-3']) !!}
        </div>
        <div class="card mb-3" id="resellOptions">
            <div class="card-body">
                {!! Form::label('Resale Value') !!} {!! add_help('This value is publicly displayed on the ' . ($isMyo ? 'MYO slot' : 'character') . '\'s page.') !!}
                {!! Form::text('sale_value', old('sale_value'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('On Transfer Cooldown Until (Optional)') !!}
            {!! Form::text('transferrable_at', old('transferrable_at'), ['class' => 'form-control datepicker']) !!}
        </div>

        <h3>Image Upload</h3>

        <div class="form-group">
            {!! Form::label('Image') !!}
            @if ($isMyo)
                {!! add_help('This is a cover image for the MYO slot. If left blank, a default image will be used.') !!}
            @else
                {!! add_help('This is the full masterlist image. Note that the image is not protected in any way, so take precautions to avoid art/design theft.') !!}
            @endif
            <div class="custom-file">
                {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                {!! Form::file('image', ['class' => 'custom-file-input', 'id' => 'mainImage']) !!}
            </div>
        </div>
        @if (config('lorekeeper.settings.masterlist_image_automation') === 1)
            <div class="form-group">
                {!! Form::checkbox('use_cropper', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'useCropper']) !!}
                {!! Form::label('use_cropper', 'Use Thumbnail Automation', ['class' => 'form-check-label ml-3']) !!} {!! add_help('A thumbnail is required for the upload (used for the masterlist). You can use the Thumbnail Automation, or upload a custom thumbnail.') !!}
            </div>
            <div class="card mb-3" id="thumbnailCrop">
                <div class="card-body">
                    <div id="cropSelect">By using this function, the thumbnail will be automatically generated from the full image.</div>
                    {!! Form::hidden('x0', 1) !!}
                    {!! Form::hidden('x1', 1) !!}
                    {!! Form::hidden('y0', 1) !!}
                    {!! Form::hidden('y1', 1) !!}
                </div>
            </div>
        @else
            <div class="form-group">
                {!! Form::checkbox('use_cropper', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'useCropper']) !!}
                {!! Form::label('use_cropper', 'Use Image Cropper', ['class' => 'form-check-label ml-3']) !!} {!! add_help('A thumbnail is required for the upload (used for the masterlist). You can use the image cropper (crop dimensions can be adjusted in the site code), or upload a custom thumbnail.') !!}
            </div>
            <div class="card mb-3" id="thumbnailCrop">
                <div class="card-body">
                    <div id="cropSelect">Select an image to use the thumbnail cropper.</div>
                    <img src="#" id="cropper" class="hide" alt="" />
                    {!! Form::hidden('x0', null, ['id' => 'cropX0']) !!}
                    {!! Form::hidden('x1', null, ['id' => 'cropX1']) !!}
                    {!! Form::hidden('y0', null, ['id' => 'cropY0']) !!}
                    {!! Form::hidden('y1', null, ['id' => 'cropY1']) !!}
                </div>
            </div>
        @endif
        <div class="card mb-3" id="thumbnailUpload">
            <div class="card-body">
                {!! Form::label('Thumbnail Image') !!} {!! add_help('This image is shown on the masterlist page.') !!}
                <div class="custom-file">
                    {!! Form::label('thumbnail', 'Choose thumbnail...', ['class' => 'custom-file-label']) !!}
                    {!! Form::file('thumbnail', ['class' => 'custom-file-input']) !!}
                </div>
                <div class="text-muted">Recommended size: {{ config('lorekeeper.settings.masterlist_thumbnails.width') }}px x {{ config('lorekeeper.settings.masterlist_thumbnails.height') }}px</div>
            </div>
        </div>
        <p class="alert alert-info">
            This section is for crediting the image creators. The first box is for the designer or artist's on-site username (if any). The second is for a link to the designer or artist if they don't have an account on the site.
        </p>
        <div class="form-group">
            {!! Form::label('Designer(s)') !!}
            <div id="designerList">
                <div class="mb-2 d-flex">
                    {!! Form::select('designer_id[]', $userOptions, null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select a Designer']) !!}
                    {!! Form::text('designer_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Designer URL']) !!}
                    <a href="#" class="add-designer btn btn-link" data-toggle="tooltip" title="Add another designer">+</a>
                </div>
            </div>
            <div class="designer-row hide mb-2">
                {!! Form::select('designer_id[]', $userOptions, null, ['class' => 'form-control mr-2 designer-select', 'placeholder' => 'Select a Designer']) !!}
                {!! Form::text('designer_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Designer URL']) !!}
                <a href="#" class="add-designer btn btn-link" data-toggle="tooltip" title="Add another designer">+</a>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('Artist(s)') !!}
            <div id="artistList">
                <div class="mb-2 d-flex">
                    {!! Form::select('artist_id[]', $userOptions, null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select an Artist']) !!}
                    {!! Form::text('artist_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                    <a href="#" class="add-artist btn btn-link" data-toggle="tooltip" title="Add another artist">+</a>
                </div>
            </div>
            <div class="artist-row hide mb-2">
                {!! Form::select('artist_id[]', $userOptions, null, ['class' => 'form-control mr-2 artist-select', 'placeholder' => 'Select an Artist']) !!}
                {!! Form::text('artist_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                <a href="#" class="add-artist btn btn-link mb-2" data-toggle="tooltip" title="Add another artist">+</a>
            </div>
        </div>
        @if (!$isMyo)
            <div class="form-group">
                {!! Form::label('Image Notes (Optional)') !!} {!! add_help('This section is for making additional notes about the image.') !!}
                {!! Form::textarea('image_description', old('image_description'), ['class' => 'form-control wysiwyg']) !!}
            </div>
        @endif

        <h3>Traits</h3>

        <div class="form-group">
            {!! Form::label('Species') !!} @if ($isMyo)
                {!! add_help('This will lock the slot into a particular species. Leave it blank if you would like to give the user a choice.') !!}
            @endif
            {!! Form::select('species_id', $specieses, old('species_id'), ['class' => 'form-control', 'id' => 'species']) !!}
        </div>

        <div class="form-group" id="subtypes">
            {!! Form::label('Subtypes (Optional)') !!} @if ($isMyo)
                {!! add_help(
                    'This will lock the slot into a particular subtype. Leave it blank if you would like to give the user a choice, or not select a subtype. The subtype must match the species selected above, and if no species is specified, the subtype will not be applied.',
                ) !!}
            @endif
            {!! Form::select('subtype_ids[]', $subtypes, old('subtype_ids'), ['class' => 'form-control disabled', 'id' => 'subtype', 'multiple', 'placeholder' => 'Pick a Species First']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('Character Rarity') !!} @if ($isMyo)
                {!! add_help('This will lock the slot into a particular rarity. Leave it blank if you would like to give the user more choices.') !!}
            @endif
            {!! Form::select('rarity_id', $rarities, old('rarity_id'), ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('Traits') !!} @if ($isMyo)
                {!! add_help(
                    'These traits will be listed as required traits for the slot. The user will still be able to add on more traits, but not be able to remove these. This is allowed to conflict with the rarity above; you may add traits above the character\'s specified rarity.',
                ) !!}
            @endif
            <div><a href="#" class="btn btn-primary mb-2" id="add-feature">Add Trait</a></div>
            <div id="featureList">
            </div>
            <div class="feature-row hide mb-2">
                {!! Form::select('feature_id[]', $features, null, ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Select Trait']) !!}
                {!! Form::text('feature_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
                <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
            </div>
        </div>

        <div class="text-right">
            {!! Form::submit('Create Character', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @endif

@endsection

@section('scripts')
    @parent
    @include('widgets._character_create_options_js')
    @include('widgets._image_upload_js')
    @include('widgets._datetimepicker_js')
    @include('widgets._character_warning_js')
    @if (!$isMyo)
        @include('widgets._character_code_js')
    @endif
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

        $("#subtype").selectize({
            maxItems: {{ config('lorekeeper.extensions.multiple_subtype_limit') }},
        });
    </script>
@endsection
