@extends('admin.layout')

@section('admin-title')
    {{ $news->id ? 'Edit' : 'Create' }} News Post
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'News' => 'admin/news', ($news->id ? 'Edit' : 'Create') . ' Post' => $news->id ? 'admin/news/edit/' . $news->id : 'admin/news/create']) !!}

    <h1>{{ $news->id ? 'Edit' : 'Create' }} News Post
        @if ($news->id)
            <a href="#" class="btn btn-danger float-end delete-news-button">Delete Post</a>
            <a href="#" class="btn btn-secondary float-end regen-news-button me-md-2">Regenerate Post</a>
            <a href="{{ $news->url }}" class="btn btn-info float-end me-md-2">View Post</a>
        @endif
    </h1>

    {!! Form::open(['url' => $news->id ? 'admin/news/edit/' . $news->id : 'admin/news/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6 mb-3">
            {!! Form::label('Title') !!}
            {!! Form::text('title', $news->title, ['class' => 'form-control']) !!}
        </div>

        <div class="col-md-6 mb-3">
            {!! Form::label('Post Time (Optional)') !!} {!! add_help('This is the time that the news post should be posted. Make sure the Is Viewable switch is off.') !!}
            {!! Form::text('post_at', $news->post_at, ['class' => 'form-control datepicker']) !!}
        </div>
    </div>

    <div class="mb-3">
        {!! Form::label('Post Content') !!}
        {!! Form::textarea('text', $news->text, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md mb-3">
            {!! Form::checkbox('is_visible', 1, $news->id ? $news->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Viewable', ['class' => 'form-check-label ms-3']) !!} {!! add_help('If this is turned off, the post will not be visible. If the post time is set, it will automatically become visible at/after the given post time, so make sure the post time is empty if you want it to be completely hidden.') !!}
        </div>
        @if ($news->id && $news->is_visible)
            <div class="col-md mb-3">
                {!! Form::checkbox('bump', 1, null, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('bump', 'Bump News', ['class' => 'form-check-label ms-3']) !!} {!! add_help('If toggled on, this will alert users that there is new news. Best in conjunction with a clear notification of changes!') !!}
            </div>
        @endif
    </div>

    <div class="text-end">
        {!! Form::submit($news->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    @include('widgets._datetimepicker_js')
    @include('js._tinymce_wysiwyg')
    <script>
        $(document).ready(function() {
            $('.delete-news-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/news/delete') }}/{{ $news->id }}", 'Delete Post');
            });
            $('.regen-news-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/news/regen') }}/{{ $news->id }}", 'Regenerate Post');
            });
        });
    </script>
@endsection
