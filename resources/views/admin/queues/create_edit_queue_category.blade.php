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
        <div class="col-md-6 form-group">
            {!! Form::label('Key (Optional)') !!} {!! add_help(
                'If set, this will cause the category to be displayed as its own "index page", and all its queues will only be displayed on that page, nowhere else. This is a unique name used to form the URL of the index page. Only alphanumeric characters, dash and underscore (no spaces) can be used.',
            ) !!}
            {!! Form::text('key', $category->key, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::checkbox('display', 1, $category->id ? $category->display : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('display', 'Display In Index', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the category will not be displayed alongside other categories, and must be linked to using its url. Good in conjunction with a key set above to isolate this category from other areas.') !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
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

    <h3>Submission Limits (Optional)</h3>
    <p>You can limit the amount of times a user can submit to queues in this category.</p>
    <p>Set a number into number of submissions. This will be applied for all time if you leave period blank, or per time period (ex: once a month, twice a week) if selected.</p>
    <p>This will apply to all queues within this category. So for example, if the limit is 3, a user will only be able to submit to queues in this category 3 times regardless of how their submissions are spread out amongst them. (2 for Queue A, 1 for
        Queue B, or 3 for Queue B and 0 for Queue A, etc).</p>

    <div class="row">
        <div class="col-md-4 form-group">
            {!! Form::label('limit', 'Number of Submissions (Optional)') !!} {!! add_help('Enter a number to limit how many times a user can submit to queues in this category. Leave blank to allow endless submissions.') !!}
            {!! Form::text('limit', $category->limit, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-4 form-group">
            {!! Form::label('limit_period', 'Limit Period') !!} {!! add_help('The time period that the limit is set for.') !!}
            {!! Form::select('limit_period', $limit_periods, $category->limit_period, ['class' => 'form-control', 'data-name' => 'limit_period']) !!}
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 form-group">
            {!! Form::label('limit_concurrent', 'Concurrent Limit (Optional)') !!} {!! add_help(
                'A limit to concurrent submissions (applies regardless of the limit above). This will check if the user has any pending or draft submissions and prevents them from making more after they hit the cap until they are processed. Leave blank to enforce no limit to concurrent submissions.',
            ) !!}
            {!! Form::text('limit_concurrent', $category->limit_concurrent, ['class' => 'form-control']) !!}
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
                @include('queues._category_entry')
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-category-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/queue-categories/delete') }}/{{ $category->id }}", 'Delete Category');
            });
        });
    </script>
@endsection
