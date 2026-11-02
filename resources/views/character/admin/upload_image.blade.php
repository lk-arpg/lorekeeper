@extends('character.layout')

@section('profile-title')
    {{ $character->fullName }} - Upload New Image
@endsection

@section('profile-content')
    {!! breadcrumbs(['Masterlist' => 'masterlist', $character->fullName => $character->url]) !!}

    @include('character._header', ['character' => $character])

    <p>This will add a new image to the character's gallery. The character's active image will be changed to the new one automatically. If the character is marked as visible, the owner of the character will be notified of the upload.</p>

    {!! Form::open(['url' => 'admin/character/' . $character->slug . '/image', 'files' => true]) !!}

    <h3>Validity</h3>

    <div class="form-group">
        {!! Form::checkbox('is_valid', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_valid', 'Is Valid', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, the image will still be visible, but displayed with a note that the image is not a valid reference.') !!}
    </div>

    <h3>Image Upload</h3>

    <div class="form-group">
        {!! Form::label('Character Image') !!} {!! add_help('This is the full masterlist image. Note that the image is not protected in any way, so take precautions to avoid art/design theft.') !!}
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
                {!! Form::select('designer_id[]', $users, null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select a Designer']) !!}
                {!! Form::text('designer_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Designer URL']) !!}
                <a href="#" class="add-designer btn btn-link" data-toggle="tooltip" title="Add another designer">+</a>
            </div>
        </div>
        <div class="designer-row hide mb-2">
            {!! Form::select('designer_id[]', $users, null, ['class' => 'form-control mr-2 designer-select', 'placeholder' => 'Select a Designer']) !!}
            {!! Form::text('designer_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Designer URL']) !!}
            <a href="#" class="add-designer btn btn-link" data-toggle="tooltip" title="Add another designer">+</a>
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('Artist(s)') !!}
        <div id="artistList">
            <div class="mb-2 d-flex">
                {!! Form::select('artist_id[]', $users, null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select an Artist']) !!}
                {!! Form::text('artist_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                <a href="#" class="add-artist btn btn-link" data-toggle="tooltip" title="Add another artist">+</a>
            </div>
        </div>
        <div class="artist-row hide mb-2">
            {!! Form::select('artist_id[]', $users, null, ['class' => 'form-control mr-2 artist-select', 'placeholder' => 'Select an Artist']) !!}
            {!! Form::text('artist_url[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
            <a href="#" class="add-artist btn btn-link mb-2" data-toggle="tooltip" title="Add another artist">+</a>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Image Notes (Optional)') !!} {!! add_help('This section is for making additional notes about the image.') !!}
        {!! Form::textarea('image_description', old('image_description'), ['class' => 'form-control wysiwyg']) !!}
    </div>

    <h3>
        {{-- <div class="float-right"><a href="#" class="btn btn-info btn-sm" data-toggle="tooltip" title="This will fill the below fields with the same data as the character's current image. Note that this will overwrite any changes made below.">Fill Data</a></div> --}}
        Traits
    </h3>

    <div class="form-group">
        {!! Form::label('Species') !!}
        {!! Form::select('species_id', $specieses, old('species_id') ?: $character->image->species_id, ['class' => 'form-control', 'id' => 'species']) !!}
    </div>

    <div class="form-group" id="subtypes">
        {!! Form::label('Subtypes (Optional)') !!}
        {!! Form::select('subtype_ids[]', $subtypes, old('subtype_ids') ?: $character->image->subtypes()?->pluck('subtype_id')->toArray(), ['class' => 'form-control', 'id' => 'subtype', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Character Rarity') !!}
        {!! Form::select('rarity_id', $rarities, old('rarity_id') ?: $character->image->rarity_id, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Traits') !!}
        <div><a href="#" class="btn btn-primary mb-2" id="add-feature">Add Trait</a></div>
        <div id="featureList">
            @if (config('lorekeeper.extensions.autopopulate_image_features'))
                @foreach ($character->image->features as $feature)
                    <div class="d-flex mb-2">
                        {!! Form::select('feature_id[]', $features, $feature->feature_id, ['class' => 'form-control mr-2 feature-select original', 'placeholder' => 'Select Trait']) !!}
                        {!! Form::text('feature_data[]', $feature->data, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
                        <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="feature-row hide mb-2">
            {!! Form::select('feature_id[]', $features, null, ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Select Trait']) !!}
            {!! Form::text('feature_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
            <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit('Create Image', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}

@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            // Cropper ////////////////////////////////////////////////////////////////////////////////////

            var $useCropper = $('#useCropper');
            var $thumbnailCrop = $('#thumbnailCrop');
            var $thumbnailUpload = $('#thumbnailUpload');

            var useCropper = $useCropper.is(':checked');

            updateCropper();

            $useCropper.on('change', function(e) {
                useCropper = $useCropper.is(':checked');

                updateCropper();
            });

            function updateCropper() {
                if (useCropper) {
                    $thumbnailUpload.addClass('hide');
                    $thumbnailCrop.removeClass('hide');
                } else {
                    $thumbnailCrop.addClass('hide');
                    $thumbnailUpload.removeClass('hide');
                }
            }

            // Designers and artists //////////////////////////////////////////////////////////////////////

            $('.selectize').selectize();
            $('.add-designer').on('click', function(e) {
                e.preventDefault();
                addDesignerRow($(this));
            });

            function addDesignerRow($trigger) {
                var $clone = $('.designer-row').clone();
                $('#designerList').append($clone);
                $clone.removeClass('hide designer-row');
                $clone.addClass('d-flex');
                $clone.find('.add-designer').on('click', function(e) {
                    e.preventDefault();
                    addDesignerRow($(this));
                })
                $clone.find('.designer-select').selectize();
                $trigger.css({
                    visibility: 'hidden'
                });
            }

            $('.add-artist').on('click', function(e) {
                e.preventDefault();
                addArtistRow($(this));
            });

            function addArtistRow($trigger) {
                var $clone = $('.artist-row').clone();
                $('#artistList').append($clone);
                $clone.removeClass('hide artist-row');
                $clone.addClass('d-flex');
                $clone.find('.add-artist').on('click', function(e) {
                    e.preventDefault();
                    addArtistRow($(this));
                })
                $clone.find('.artist-select').selectize();
                $trigger.css({
                    visibility: 'hidden'
                });
            }

            // Traits /////////////////////////////////////////////////////////////////////////////////////

            $('#add-feature').on('click', function(e) {
                e.preventDefault();
                addFeatureRow();
            });
            $('.remove-feature').on('click', function(e) {
                e.preventDefault();
                removeFeatureRow($(this));
            })

            function addFeatureRow() {
                var $clone = $('.feature-row').clone();
                $('#featureList').append($clone);
                $clone.removeClass('hide feature-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-feature').on('click', function(e) {
                    e.preventDefault();
                    removeFeatureRow($(this));
                })
                @if (config('lorekeeper.extensions.organised_traits_dropdown.enable'))
                    $clone.find('.feature-select').selectize({
                        render: {
                            item: featureSelectedRender
                        }
                    });
                @else
                    $clone.find('.feature-select').selectize();
                @endif
            }

            function removeFeatureRow($trigger) {
                $trigger.parent().remove();
            }

            function featureOptionRender(item, escape) {
                return '<div class="option"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + (item["text"].trim()) + '</span></div>';
            }

            function featureSelectedRender(item, escape) {
                @if (config('lorekeeper.extensions.organised_traits_dropdown.rarity.enable'))
                    return '<div><span>' + (item["text"].trim()) + ' (' + (item["optgroup"].trim()) + ')' + '</span></div>';
                @endif
                return '<div><span>' + escape(item["text"].trim()) + ' (' + escape(item["optgroup"].trim()) + ')' + '</span></div>';
            }

            // Croppie ////////////////////////////////////////////////////////////////////////////////////

            var thumbnailWidth = {{ config('lorekeeper.settings.masterlist_thumbnails.width') }};
            var thumbnailHeight = {{ config('lorekeeper.settings.masterlist_thumbnails.height') }};
            var $cropper = $('#cropper');
            var c = null;
            var $x0 = $('#cropX0');
            var $y0 = $('#cropY0');
            var $x1 = $('#cropX1');
            var $y1 = $('#cropY1');
            var zoom = 0;

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $cropper.attr('src', e.target.result);
                        c = new Croppie($cropper[0], {
                            viewport: {
                                width: thumbnailWidth,
                                height: thumbnailHeight
                            },
                            boundary: {
                                width: thumbnailWidth + 100,
                                height: thumbnailHeight + 100
                            },
                            update: function() {
                                updateCropValues();
                            }
                        });
                        updateCropValues();
                        $('#cropSelect').addClass('hide');
                        $cropper.removeClass('hide');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#mainImage").change(function() {
                readURL(this);
            });

            function updateCropValues() {
                var values = c.get();
                $x0.val(values.points[0]);
                $y0.val(values.points[1]);
                $x1.val(values.points[2]);
                $y1.val(values.points[3]);
            }


        });

        $("#species").change(function() {
            var species = $('#species').val();
            var id = '<?php echo $character->image->id; ?>';
            $.ajax({
                type: "GET",
                url: "{{ url('admin/character/image/subtype') }}?species=" + species + "&id=" + id,
                dataType: "text"
            }).done(function(res) {
                $("#subtypes").html(res);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        });

        $('#subtype').selectize({
            maxItems: {{ config('lorekeeper.extensions.multiple_subtype_limit') }},
        });
    </script>
@endsection
