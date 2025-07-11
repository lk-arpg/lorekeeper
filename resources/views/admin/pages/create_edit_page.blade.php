@extends('admin.layout')

@section('admin-title')
    {{ $page->id ? 'Edit' : 'Create' }} Page
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Pages' => 'admin/pages', ($page->id ? 'Edit' : 'Create') . ' Page' => $page->id ? 'admin/pages/edit/' . $page->id : 'admin/pages/create']) !!}

    <h1>{{ $page->id ? 'Edit' : 'Create' }} Page
        @if ($page->id && !config('lorekeeper.text_pages.' . $page->key))
            <a href="#" class="btn btn-danger float-right delete-page-button">Delete Page</a>
        @endif
        @if ($page->id)
            <a href="#" class="btn btn-secondary float-right regen-page-button mr-md-2">Regenerate Page</a>
            <a href="{{ $page->url }}" class="btn btn-info float-right mr-md-2">View Page</a>
        @endif
    </h1>

    {!! Form::open(['url' => $page->id ? 'admin/pages/edit/' . $page->id : 'admin/pages/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Title') !!}
            {!! Form::text('title', $page->title, ['class' => 'form-control']) !!}
        </div>

        <div class="col-md-6 form-group">
            {!! Form::label('Key') !!} {!! add_help('This is a unique name used to form the URL of the page. Only alphanumeric characters, dash and underscore (no spaces) can be used.') !!}
            {!! Form::text('key', $page->key, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Header Image (Optional)') !!} {!! add_help('This image will show up above the page content and on the meta-image.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        @if ($page->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Page Content') !!}
        {!! Form::textarea('text', $page->text, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            {!! Form::checkbox('is_visible', 1, $page->id ? $page->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Viewable', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, users will not be able to view the page even if they have the link to it.') !!}
        </div>

        <div class="col-md-4 form-group">
            {!! Form::checkbox('can_comment', 1, $page->id ? $page->can_comment : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('can_comment', 'Commentable', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned on, users will be able to comment on the page.') !!}
            @if (!Settings::get('comment_dislikes_enabled'))
                <div class="form-group">
                    {!! Form::checkbox('allow_dislikes', 1, $page->id ? $page->allow_dislikes : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('allow_dislikes', 'Allow Dislikes On Comments?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, users cannot dislike comments.') !!}
                </div>
            @endif
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($page->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-page-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/pages/delete') }}/{{ $page->id }}", 'Delete Page');
            });
            $('.regen-page-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/pages/regen') }}/{{ $page->id }}", 'Regenerate Page');
            });
        });
    </script>
@endsection
