{!! Form::open(['url' => 'admin/character/image/' . $image->id . '/reupload', 'files' => true]) !!}
<div class="mb-3">
    {!! Form::label('Character Image') !!} {!! add_help('This is the full masterlist image. Note that the image is not protected in any way, so take precautions to avoid art/design theft.') !!}
    <div class="custom-file">
        {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
        {!! Form::file('image', ['class' => 'custom-file-input', 'id' => 'mainImage']) !!}
    </div>
</div>
@if (config('lorekeeper.settings.masterlist_image_automation') === 1)
    <div class="mb-3">
        {!! Form::checkbox('use_cropper', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'useCropper']) !!}
        {!! Form::label('use_cropper', 'Use Thumbnail Automation', ['class' => 'form-check-label ms-3']) !!} {!! add_help('A thumbnail is required for the upload (used for the masterlist). You can use the Thumbnail Automation, or upload a custom thumbnail.') !!}
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
    <div class="mb-3">
        {!! Form::checkbox('use_cropper', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'useCropper']) !!}
        {!! Form::label('use_cropper', 'Use Image Cropper', ['class' => 'form-check-label ms-3']) !!} {!! add_help('A thumbnail is required for the upload (used for the masterlist). You can use the image cropper (crop dimensions can be adjusted in the site code), or upload a custom thumbnail.') !!}
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

<div class="text-end">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        //$('#useCropper').bootstrapToggle();
        bsCustomFileInput.init();
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
</script>
