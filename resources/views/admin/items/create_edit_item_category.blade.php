@extends('admin.layout')

@section('admin-title')
    {{ $category->id ? 'Edit' : 'Create' }} Item Category
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Item Categories' => 'admin/data/item-categories',
        ($category->id ? 'Edit' : 'Create') . ' Category' => $category->id ? 'admin/data/item-categories/edit/' . $category->id : 'admin/data/item-categories/create',
    ]) !!}

    <h1>{{ $category->id ? 'Edit' : 'Create' }} Item Category
        @if ($category->id)
            <a href="#" class="btn btn-danger float-right delete-category-button">Delete Category</a>
        @endif
    </h1>

    {!! Form::open(['url' => $category->id ? 'admin/data/item-categories/edit/' . $category->id : 'admin/data/item-categories/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $category->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($category->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $category->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $category->id ? $category->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the category will not be visible in the category list or available for selection in search. Permissioned staff will still be able to add items to them, however.') !!}
    </div>

    <div class="card mb-3" id="characterOptions">
        <div class="card-body">
            <div class="mb-2">
                <div class="form-group">
                    {!! Form::checkbox('is_character_owned', 1, $category->is_character_owned, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Allow', 'data-off' => 'Disallow']) !!}
                    {!! Form::label('is_character_owned', 'Can Be Owned by Characters', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will allow items in this category to be owned by characters.') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('character_limit', 'Character Hold Limit') !!} {!! add_help('This is the maximum amount of items from this category a character can possess. Set to 0 to allow infinite.') !!}
                    {!! Form::text('character_limit', $category ? $category->character_limit : 0, ['class' => 'form-control stock-field', 'data-name' => 'character_limit']) !!}
                </div>
                <div class="form-group">
                    {!! Form::checkbox('can_name', 1, $category->can_name, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Allow', 'data-off' => 'Disallow']) !!}
                    {!! Form::label('can_name', 'Can be Named', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will set items in this category to be able to be named when in character inventories-- for instance, for pets. Works best in conjunction with a hold limit on the category.') !!}
                </div>
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($category->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($category->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._item_category_entry', ['imageUrl' => $category->categoryImageUrl, 'name' => $category->displayName, 'description' => $category->parsed_description, 'category' => $category])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-category-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/item-categories/delete') }}/{{ $category->id }}", 'Delete Category');
            });
        });
    </script>
@endsection
