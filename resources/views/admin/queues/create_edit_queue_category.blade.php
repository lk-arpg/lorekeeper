@extends('admin.layout')

@section('admin-title')
    {{ $category->id ? 'Edit' : 'Create' }} Queue Category
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Queue Categories' => 'admin/data/queue-categories',
        ($category->id ? 'Edit' : 'Create') . ' Category' => $category->id ? 'admin/data/queue-categories/edit/' . $category->id : 'admin/data/queue-categories/create',
    ]) !!}

    <h1>{{ $category->id ? 'Edit' : 'Create' }} Queue Category
        @if ($category->id)
            <a href="#" class="btn btn-danger float-right delete-category-button">Delete Category</a>
        @endif
    </h1>

    {!! Form::open(['url' => $category->id ? 'admin/data/queue-categories/edit/' . $category->id : 'admin/data/queue-categories/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $category->name, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-3 form-group">
            {!! Form::label('Key (Optional)') !!} {!! add_help(
                'If set, this will cause the category to be displayed as its own \'index page\', and all its queues will only be displayed on that page, nowhere else. This is a unique name used to form the URL of the index page. Only alphanumeric characters, dash and underscore (no spaces) can be used.',
            ) !!}
            {!! Form::text('key', $category->key, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-3 form-group d-flex align-items-end">
            {!! Form::checkbox('display', 1, $category->id ? $category->display : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('display', 'Display In Index', ['class' => 'form-check-label ml-3 mb-2']) !!}
            <span class="mb-2">
                {!! add_help('If turned off, the category will not be displayed alongside other categories, and must be linked to using its url. Good in conjunction with a key set above to isolate this category from other areas.') !!}
            </span>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
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

    @include('widgets._add_submission_limits', ['object' => $category])

    <div class="text-right">
        {!! Form::submit($category->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($category->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('queues._category_entry')
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
                loadModal("{{ url('admin/data/queue-categories/delete') }}/{{ $category->id }}", 'Delete Category');
            });
        });
    </script>
@endsection
