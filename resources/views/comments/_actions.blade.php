{{-- Action buttons --}}
@if (Auth::check())
    <div class="my-1 row justify-content-between no-gutters">
        <div class="col-auto">
            @can('reply-to-comment', $comment)
                <button data-toggle="modal" data-target="#reply-modal-{{ $comment->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1 btn-faded text-uppercase"><i class="fas fa-comment"></i><span
                        class="ml-2 d-none d-sm-inline-block">Reply</span></button>
            @endcan
            @can('edit-comment', $comment)
                <button data-toggle="modal" data-target="#comment-modal-{{ $comment->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1 btn-faded text-uppercase"><i class="fas fa-edit"></i><span
                        class="ml-2 d-none d-sm-inline-block">Edit</span></button>
            @endcan
            @if (((Auth::user()->id == $comment->commentable_id && $comment->commentable_type == 'App\Models\User\UserProfile') || Auth::user()->isStaff) && (isset($compact) && !$compact))
                <button data-toggle="modal" data-target="#feature-modal-{{ $comment->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1 btn-faded text-success text-uppercase"><i class="fas fa-star"></i><span
                        class="ml-2 d-none d-sm-inline-block">{{ $comment->is_featured ? 'Unf' : 'F' }}eature Comment</span></button>
            @endif
            @can('delete-comment', $comment)
                <button data-toggle="modal" data-target="#delete-modal-{{ $comment->getKey() }}" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1 btn-outline-danger text-uppercase"><i class="fas fa-minus-circle"></i><span
                        class="ml-2 d-none d-sm-inline-block">Delete</span></button>
            @endcan
        </div>
        <div class="col-auto text-right">
            {{-- Likes Section --}}
            <a href="#" data-toggle="modal" data-target="#show-likes-{{ $comment->id }}">
                <button href="#" data-toggle="tooltip" title="Click to View" class="btn btn-sm px-3 py-2 px-sm-2 py-sm-1 btn-faded">
                    {{ $comment->likes()->where('is_like', 1)->count() - $comment->likes()->where('is_like', 0)->count() }}
                    {{ $comment->likes()->where('is_like', 1)->count() - $comment->likes()->where('is_like', 0)->count() != 1 ? 'Likes' : 'Like' }}
                </button>
            </a>
            {!! Form::open(['url' => 'comments/' . $comment->id . '/like/1', 'class' => 'd-inline-block']) !!}
            {!! Form::button('<i class="fas fa-thumbs-up"></i>', [
                'type' => 'submit',
                'class' =>
                    'btn btn-sm px-3 py-2 px-sm-2 py-sm-1 ' .
                    ($comment->likes()->where('user_id', Auth::user()->id)->where('is_like', 1)->exists()
                        ? 'btn-success'
                        : 'btn-outline-success') .
                    ' text-uppercase',
            ]) !!}
            {!! Form::close() !!}
            @if (Settings::get('comment_dislikes_enabled') || (isset($allow_dislikes) && $allow_dislikes))
                {!! Form::open(['url' => 'comments/' . $comment->id . '/like/0', 'class' => 'd-inline-block']) !!}
                {!! Form::button('<i class="fas fa-thumbs-down"></i>', [
                    'type' => 'submit',
                    'class' =>
                        'btn btn-sm px-3 py-2 px-sm-2 py-sm-1 ' .
                        ($comment->likes()->where('user_id', Auth::user()->id)->where('is_like', 0)->exists()
                            ? 'btn-danger'
                            : 'btn-outline-danger') .
                        ' text-uppercase',
                ]) !!}
                {!! Form::close() !!}
            @endif
        </div>
    </div>
@endif

{{-- Modals --}}
@can('edit-comment', $comment)
    <div class="modal fade" id="comment-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                {{ Form::model($comment, ['route' => ['comments.update', $comment->getKey()]]) }}
                <div class="modal-header">
                    <h5 class="modal-title">Edit Comment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('message', 'Update your message here:') !!}
                        {!! Form::textarea('message', $comment->comment, ['class' => 'form-control ' . (config('lorekeeper.settings.wysiwyg_comments') ? 'comment-wysiwyg' : ''), 'rows' => 3, config('lorekeeper.settings.wysiwyg_comments') ? '' : 'required']) !!}
                        <small class="form-text text-muted"><a target="_blank" href="https://help.github.com/articles/basic-writing-and-formatting-syntax">Markdown</a> cheatsheet.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase" data-dismiss="modal">Cancel</button>
                    {!! Form::submit('Update', ['class' => 'btn btn-sm btn-outline-success text-uppercase']) !!}
                </div>
                </form>
            </div>
        </div>
    </div>
@endcan
{{-- modal large --}}
@can('reply-to-comment', $comment)
    <div class="modal fade" id="reply-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                {{ Form::open(['route' => ['comments.reply', $comment->getKey()]]) }}
                <div class="modal-header">
                    <h5 class="modal-title">Reply to Comment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('message', 'Enter your message here:') !!}
                        {!! Form::textarea('message', null, ['class' => 'form-control ' . (config('lorekeeper.settings.wysiwyg_comments') ? 'comment-wysiwyg' : ''), 'rows' => 3, config('lorekeeper.settings.wysiwyg_comments') ? '' : 'required']) !!}
                        <small class="form-text text-muted"><a target="_blank" href="https://help.github.com/articles/basic-writing-and-formatting-syntax">Markdown</a> cheatsheet.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary text-uppercase" data-dismiss="modal">Cancel</button>
                    {!! Form::submit('Reply', ['class' => 'btn btn-sm btn-outline-success text-uppercase']) !!}
                </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@can('delete-comment', $comment)
    <div class="modal fade" id="delete-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Comment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">Are you sure you want to delete this comment?</div>
                    <div class="alert alert-warning">
                        <strong>Comments can be restored in the database.</strong>
                        <br> Deleting a comment does not delete the comment record.
                    </div>
                    <div class="text-right">
                        <a href="{{ route('comments.destroy', $comment->getKey()) }}" onclick="event.preventDefault();document.getElementById('comment-delete-form-{{ $comment->getKey() }}').submit();" class="btn btn-danger text-uppercase">Delete</a>
                        <form id="comment-delete-form-{{ $comment->getKey() }}" action="{{ route('comments.destroy', $comment->getKey()) }}" method="POST" style="display: none;">
                            @method('DELETE')
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcan

<div class="modal fade" id="feature-modal-{{ $comment->getKey() }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $comment->is_featured ? 'Unf' : 'F' }}eature Comment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">Are you sure you want to {{ $comment->is_featured ? 'un' : '' }}feature this comment?</div>
            </div>
            <div class="alert alert-warning">Comments can be unfeatured.</div>
            {!! Form::open(['url' => 'comments/' . $comment->id . '/feature']) !!}
            @if (!$comment->is_featured)
                {!! Form::submit('Feature', ['class' => 'btn btn-primary w-100 mb-0 mx-0']) !!}
            @else
                {!! Form::submit('Unfeature', ['class' => 'btn btn-primary w-100 mb-0 mx-0']) !!}
            @endif
            {!! Form::close() !!}
        </div>
    </div>
</div>

<div class="modal fade" id="show-likes-{{ $comment->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Likes</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if (count($comment->likes) > 0)
                    <div class="mb-4 logs-table">
                        <div class="logs-table-header">
                            <div class="row">
                                <div class="col-4 col-md-3">
                                    <div class="logs-table-cell">User</div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="logs-table-cell"></div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="logs-table-cell"></div>
                                </div>
                            </div>
                        </div>
                        <div class="logs-table-body">
                            @foreach ($comment->likes as $like)
                                <div class="logs-table-row">
                                    <div class="row flex-wrap">
                                        <div class="col-4 col-md-3">
                                            <div class="logs-table-cell"><img style="max-height: 2em;" src="{{ $like->user->avatarUrl }}" /></div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="logs-table-cell">{!! $like->user->displayName !!}</div>
                                        </div>
                                        <div class="col-4 col-md-4 text-right">
                                            <div class="logs-table-cell">{!! $like->is_like ? '<i class="fas fa-thumbs-up"></i>' : '<i class="fas fa-thumbs-down"></i>' !!}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">No likes yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- edits modal --}}
{{-- the button for this appears in the main view, but to keep it from being cluttered we will keep the models within this section --}}
@if (Auth::check() && Auth::user()->isStaff)
    <div class="modal fade" id="show-edits-{{ $comment->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit History</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if (count($comment->edits) > 0)
                        <div class="mb-4 logs-table">
                            <div class="logs-table-header">
                                <div class="row">
                                    <div class="col-4 col-md-3">
                                        <div class="logs-table-cell">Time</div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="logs-table-cell">Old Comment</div>
                                    </div>
                                    <div class="col-4 col-md-3">
                                        <div class="logs-table-cell">New Comment</div>
                                    </div>
                                </div>
                            </div>
                            <div class="logs-table-body">
                                @foreach ($comment->edits as $edit)
                                    <div class="logs-table-row">
                                        <div class="row flex-wrap">
                                            <div class="col-4 col-md-3">
                                                <div class="logs-table-cell">
                                                    {!! format_date($edit->created_at) !!}
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="logs-table-cell">
                                                    <span data-toggle="tooltip" title="{{ $edit->data['old_comment'] }}">
                                                        {{ Str::limit($edit->data['old_comment'], 50) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="logs-table-cell">
                                                    <span data-toggle="tooltip" title="{{ $edit->data['new_comment'] }}">
                                                        {{ Str::limit($edit->data['new_comment'], 50) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">No edits yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
