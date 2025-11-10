@inject('markdown', 'Parsedown')
@php
    $markdown->setSafeMode(true);
@endphp

@if ($comment->deleted_at == null)
    @if (isset($reply) && $reply === true)
        <div id="comment-{{ $comment->getKey() }}" class="comment_replies border-left col-12 column mw-100 pr-0 pt-4" style="flex-basis: 100%;">
        @else
            <div id="comment-{{ $comment->getKey() }}" class="pt-4" style="flex-basis: 100%;">
    @endif
    <div class="media-body row mw-100 mx-0" style="flex:1;flex-wrap:wrap;">
        <div class="d-none d-md-block">
            <img class="mr-3 mt-2" src="{{ $comment->commenter->avatarUrl }}" style="width:70px; height:70px; border-radius:50%;" alt="{{ $comment->commenter->name }}'s Avatar">
        </div>
        <div class="d-block" style="flex:1">
            <div class="row mx-0 px-0 align-items-md-end">
                <h5 class="mt-0 mb-1 col mx-0 px-0">
                    {!! $comment->commenter->commentDisplayName !!} @if ($comment->commenter->isStaff == true)
                        <small class="text-muted">Staff Member</small>
                    @endif
                </h5>
                @if ($comment->is_featured)
                    <div class="ml-1 text-muted text-right col-6 mx-0 pr-1"><small class="text-success">Featured by Owner</small></div>
                @endif
            </div>
            <div
                class="comment border p-3 rounded {{ $limit == 0 ? 'shadow-sm border-info' : '' }} {{ $comment->is_featured && $limit != 0 ? 'border-success' : '' }} {{ $comment->likes()->where('is_like', 1)->count() - $comment->likes()->where('is_like', 0)->count() < 0 ? 'bg-light bg-gradient' : '' }}">
                <p>
                    {!! config('lorekeeper.settings.wysiwyg_comments') ? $comment->comment : '<p>' . nl2br($markdown->line(strip_tags($comment->comment))) . '</p>' !!}
                </p>
                <p class="border-top pt-1 text-right mb-0">
                    <small class="text-muted">{!! $comment->created_at !!}
                        @if ($comment->created_at != $comment->updated_at)
                            <span class="text-muted border-left mx-1 px-1">(Edited {!! $comment->updated_at !!})
                                @if (Auth::check() && Auth::user()->isStaff)
                                    <a href="#" data-toggle="modal" data-target="#show-edits-{{ $comment->id }}">Edit History</a>
                                @endif
                            </span>
                        @endif
                    </small>
                    <a href="{{ url('comment/') . '/' . $comment->id }}"><i class="fas fa-link ml-1" style="opacity: 50%;"></i></a>
                    <a href="{{ url('reports/new?url=') . $comment->url }}"><i class="fas fa-exclamation-triangle" data-toggle="tooltip" title="Click here to report this comment." style="opacity: 50%;"></i></a>
                </p>
            </div>

            @include('comments._actions', ['comment' => $comment, 'compact' => isset($compact) ? $compact : false])

        </div>

        {{-- add a limit check so if limit is reached but replies are still presnt to display a button with current amount of replies
use child function
url should be equal to the last replies permalink (e.g reply 5) --}}


        {{-- Recursion for children --}}
        <div class="w-100 mw-100">
            @php $children = $depth == 0 ? $comment->children->sortByDesc('created_at')->paginate(5) : $comment->children->sortByDesc('created_at') @endphp
            @foreach ($children as $reply)
                @php $limit++; @endphp

                @if ($limit >= 5 && $depth >= 1)
                    <a href="{{ url('comment/') . '/' . $comment->id }}"><span class="btn btn-secondary w-100">See More Replies</span></a>
                    @break
                @endif

                @include('comments._perma_comments', [
                    'comment' => $reply,
                    'reply' => true,
                    'limit' => $limit,
                    'depth' => $depth + 1,
                ])
            @endforeach
            @if ($depth == 0)
                {!! $children->render() !!}
            @endif
        </div>
    </div>
    </div>
@else
    @if (isset($reply) && $reply === true)
        <div id="comment-{{ $comment->getKey() }}" class="comment_replies border-left col-12 column mw-100 pr-0 pt-4" style="flex-basis: 100%;">
        @else
            <div id="comment-{{ $comment->getKey() }}" class="pt-4" style="flex-basis: 100%;">
    @endif
    <div class="media-body row mw-100 mx-0" style="flex:1;flex-wrap:wrap;">
        <div class="d-none d-md-block">
            <img class="mr-3 mt-2" src="{{ asset('images/avatars/default.jpg') }}" style="width:70px; height:70px; border-radius:50%;" alt="Default Avatar">
        </div>
        <div class="d-block bg-light" style="flex:1">
            <div class="border p-3 rounded {{ $limit == 0 ? 'shadow-sm border-info' : '' }}">
                <p>Comment deleted </p>
                <p class="border-top pt-1 text-right mb-0">
                    <small class="text-muted">{!! $comment->created_at !!}
                        @if ($comment->created_at != $comment->deleted_at)
                            <span class="text-muted border-left mx-1 px-1">(Deleted {!! $comment->deleted_at !!})</span>
                        @endif
                    </small>
                    <a href="{{ url('comment/') . '/' . $comment->id }}"><i class="fas fa-link ml-1" style="opacity: 50%;"></i></a>
                </p>
            </div>
        </div>

        {{-- Recursion for children --}}
        <div class="w-100 mw-100">
            @php $children = $depth == 0 ? $comment->children->sortByDesc('created_at')->paginate(5) : $comment->children->sortByDesc('created_at') @endphp
            @foreach ($children as $reply)
                @php $limit++; @endphp

                @if ($limit >= 5 && $depth >= 1)
                    <a href="{{ url('comment/') . '/' . $comment->id }}"><span class="btn btn-secondary w-100">See More Replies</span></a>
                    @break
                @endif

                @include('comments._perma_comments', [
                    'comment' => $reply,
                    'reply' => true,
                    'limit' => $limit,
                    'depth' => $depth + 1,
                ])
            @endforeach
            @if ($depth == 0)
                {!! $children->render() !!}
            @endif
        </div>
    </div>
    </div>
@endif
