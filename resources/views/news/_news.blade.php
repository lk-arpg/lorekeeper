<div class="card mb-3">
    <div class="card-header">
        <x-admin-edit title="News Post" :object="$news" />
        <h2 class="card-title mb-0">
            @if (!$news->is_visible)
                <i class="fas fa-eye-slash me-1"></i>
            @endif
            {!! $news->displayName !!}
        </h2>
        <small>
            Posted {!! $news->post_at ? pretty_date($news->post_at) : pretty_date($news->created_at) !!} :: Last edited {!! pretty_date($news->updated_at) !!} by {!! $news->user->displayName !!}
        </small>
    </div>
    <div class="card-body">
        <div class="parsed-text">
            {!! $news->parsed_text !!}
        </div>
    </div>
    <?php $commentCount = App\Models\Comment\Comment::where('commentable_type', 'App\Models\News')->where('commentable_id', $news->id)->count(); ?>
    @if (!$page)
        <div class="text-end mb-2 me-2">
            <a class="btn" href="{{ $news->url }}#comment-comments"><i class="fas fa-comment"></i> {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : '' }}</a>
        </div>
    @else
        <div class="text-end mb-2 me-2">
            <a class="btn" href="#comment-comments"><i class="fas fa-comment"></i> {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : '' }}</a>
        </div>
    @endif
</div>
