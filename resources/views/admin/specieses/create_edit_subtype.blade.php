@extends('admin.layout')

@section('admin-title')
    {{ $subtype->id ? 'Edit' : 'Create' }} Subtype
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Subtypes' => 'admin/data/subtypes', ($subtype->id ? 'Edit' : 'Create') . ' Subtype' => $subtype->id ? 'admin/data/subtypes/edit/' . $subtype->id : 'admin/data/subtypes/create']) !!}

    <h1>{{ $subtype->id ? 'Edit' : 'Create' }} Subtype
        @if ($subtype->id)
            <a href="#" class="btn btn-danger float-right delete-subtype-button">Delete Subtype</a>
        @endif
    </h1>

    {!! Form::open(['url' => $subtype->id ? 'admin/data/subtypes/edit/' . $subtype->id : 'admin/data/subtypes/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $subtype->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Species') !!}
        {!! Form::select('species_id', $specieses, $subtype->species_id, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($subtype->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $subtype->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $subtype->id ? $subtype->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the subtype will not be visible in the subtypes list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
    </div>
    <div class="text-right">
        {!! Form::submit($subtype->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($subtype->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._subtype_entry', ['subtype' => $subtype])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-subtype-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/subtypes/delete') }}/{{ $subtype->id }}", 'Delete Subtype');
            });
        });
    </script>
@endsection
