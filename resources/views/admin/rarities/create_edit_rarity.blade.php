@extends('admin.layout')

@section('admin-title')
    {{ $rarity->id ? 'Edit' : 'Create' }} Rarity
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Rarities' => 'admin/data/rarities', ($rarity->id ? 'Edit' : 'Create') . ' Rarity' => $rarity->id ? 'admin/data/rarities/edit/' . $rarity->id : 'admin/data/rarities/create']) !!}

    <h1>{{ $rarity->id ? 'Edit' : 'Create' }} Rarity
        @if ($rarity->id)
            <a href="#" class="btn btn-danger float-right delete-rarity-button">Delete Rarity</a>
        @endif
    </h1>

    {!! Form::open(['url' => $rarity->id ? 'admin/data/rarities/edit/' . $rarity->id : 'admin/data/rarities/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $rarity->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Colour (Hex code; optional)') !!}
        <div class="input-group cp">
            {!! Form::text('color', $rarity->color, ['class' => 'form-control']) !!}
            <span class="input-group-append">
                <span class="input-group-text colorpicker-input-addon"><i></i></span>
            </span>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($rarity->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $rarity->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($rarity->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($rarity->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._rarity_entry', [
                    'imageUrl' => $rarity->rarityImageUrl,
                    'name' => $rarity->displayName,
                    'description' => $rarity->parsed_description,
                    'searchItemsUrl' => $rarity->searchItemsUrl,
                    'searchFeaturesUrl' => $rarity->searchFeaturesUrl,
                    'searchCharactersUrl' => $rarity->searchCharactersUrl,
                    'edit' => [
                        'title' => 'Edit Rarity',
                        'object' => $rarity,
                    ],
                ])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-rarity-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/rarities/delete') }}/{{ $rarity->id }}", 'Delete Rarity');
            });
        });
    </script>
@endsection
