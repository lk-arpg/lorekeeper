@extends('layouts.app')

@section('title')
    Comments
@endsection

@section('profile-title')
    Comment
@endsection

@section('content')
    <h1>Comments on {!! $comment->commentable_type == 'App\Models\User\UserProfile' ? $comment->commentable->user->displayName : $comment->commentable->displayName !!}</h1>
    <h5>
        @if (isset($comment->child_id))
            <a href="{{ url('comment/') . '/' . $comment->child_id }}" class="btn btn-secondary btn-sm mr-2">See Parent</a>
            <a href="{{ url('comment/') . '/' . $comment->topComment->id }}" class="btn btn-secondary btn-sm mr-2">Go To Top Comment</a>
        @endif
    </h5>

    <hr>
    <br>
    <div class="d-flex mw-100 row mx-0" style="overflow:hidden;">
        @include('comments._perma_comments', ['comment' => $comment, 'limit' => 0, 'depth' => 0])
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            tinymce.init({
                selector: '.comment-wysiwyg',
                height: 300,
                menubar: false,
                convert_urls: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen spoiler',
                    'insertdatetime media table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | spoiler-add spoiler-remove | removeformat | code',
                content_css: [
                    '{{ asset('css/app.css') }}',
                    '{{ asset('css/lorekeeper.css') }}',
                    '{{ asset('css/all.min.css') }}', // fontawesome
                ],
                spoiler_caption: 'Toggle Spoiler',
                extended_valid_elements: '#i[class],#em[class]',
                target_list: false
            });
        });
    </script>
@endsection
