@php
    $altText = hasAltText($object, $text_key) ? getAltText($object, $text_key) : null;

    if (!isset($keytitle)) {
        $keytitle = '';
    }
@endphp

<div class="card p-4 mb-3 mt-3" id="text-card">
    <h3>{{ $altText ? 'Edit' : 'Create' }} {{ $keytitle }} {{ ucfirst($type) }} Alt Text</h3>

    <p>
        Consider adding alt text to this {{ $type }}'s {{ $text_key }} image via this widget. It will be a text substitute to this image that will be used by screenreaders or when the image is unavailable. Adding alt text is important to
        help visually impaired users!
    </p>
    <p>
        <a target="_blank" href="https://webaim.org/techniques/alttext/">A guide</a> on how to write alt text.
    </p>
    {!! isset($info) ? '<div class="alert alert-info">' . $info . '</div>' : '' !!}


    @if (!isset($text_key))
        <p>A text key was not provided in the blade files.</p>
    @else
        {!! Form::open(['url' => 'admin/alt-text']) !!}
        {!! Form::hidden('object_model', get_class($object)) !!}
        {!! Form::hidden('object_id', $object->id) !!}
        {!! Form::hidden('text_key', $text_key) !!}

        <div class="form-group">
            {!! Form::label('Alt Text') !!} {!! add_help('HTML cannot be used here. Limit 500 characters.') !!}
            {!! Form::text('alt_text', $altText ? $altText->alt_text : '', ['class' => 'form-control', 'maxLength' => 500]) !!}
        </div>

        <div>
            @if ($altText)
                <i class="fas fa-trash text-danger float-right mt-2 mx-2 fa-2x" data-toggle="tooltip" title="To remove alt text, simply delete the text and click 'Edit Alt Text'."></i>
            @endif
            {!! Form::submit(($altText ? 'Edit' : 'Create') . ' Alt Text', ['class' => 'btn btn-primary float-right']) !!}
        </div>

        {!! Form::close() !!}

    @endif
</div>
