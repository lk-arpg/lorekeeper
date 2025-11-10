@inject('markdown', 'Parsedown')
@php
    $markdown->setSafeMode(true);
@endphp

@if ($comment->deleted_at == null)
    <div id="comment-{{ $comment->getKey() }}" class="{{ isset($reply) && $reply === true ? 'comment_replies border-left col-12 column mw-100 pr-0' : '' }} pt-4" style="flex-basis: 100%;">
        <div class="media-body row mw-100 mx-0" style="flex:1;flex-wrap:wrap;">
            {{-- Show avatar if not compact --}}
            @if (isset($compact) && !$compact)
                <div class="d-none d-md-block">
                    <img class="mr-3 mt-2" src="{{ $comment->commenter->avatarUrl }}" style="width:70px; height:70px; border-radius:50%;" alt="{{ $comment->commenter->name }} Avatar">
                </div>
            @endif

            {{-- Main comment block --}}
            <div class="d-block" style="flex:1">

                {{-- Comment block header --}}
                <div class="row mx-0 px-0 align-items-md-end">
                    <h5 class="mt-0 mb-1 col mx-0 px-0">
                        {!! $comment->commenter->commentDisplayName !!} @if ($comment->commenter->isStaff == true)
                            <small class="text-success">Staff Member</small>
                        @endif
                    </h5>
                    @if ($comment->is_featured)
                        <div class="ml-1 text-muted text-right col-6 mx-0 pr-1"><small class="text-success">Featured by Owner</small></div>
                    @endif
                </div>

                {{-- Comment --}}
                <div
                    class="comment border p-3 rounded {{ $comment->is_featured ? 'border-success bg-light' : '' }} {{ $comment->likes()->where('is_like', 1)->count() - $comment->likes()->where('is_like', 0)->count() < 0 ? 'bg-light bg-gradient' : '' }}">
                    {!! config('lorekeeper.settings.wysiwyg_comments') ? $comment->comment : '<p>' . nl2br($markdown->line(strip_tags($comment->comment))) . '</p>' !!}
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
                        @if ($comment->type == 'User-User')
                            <a href="{{ url('comment/') . '/' . $comment->id }}"><i class="fas fa-link ml-1" style="opacity: 50%;"></i></a>
                        @endif
                        <a href="{{ url('reports/new?url=') . $comment->url }}"><i class="fas fa-exclamation-triangle" data-toggle="tooltip" title="Click here to report this comment." style="opacity: 50%;"></i></a>
                    </p>
                </div>

                @include('comments._actions', ['comment' => $comment, 'compact' => isset($compact) ? $compact : false])

            </div>

            {{-- Recursion for children --}}
            <div class="mt-3 w-100 mw-100">
                @if ($grouped_comments->has($comment->getKey()))
                    @foreach ($grouped_comments[$comment->getKey()] as $child)
                        @php $limit++; @endphp

                        @if ($limit >= 3)
                            <a href="{{ url('comment/') . '/' . $comment->id }}"><span class="btn btn-secondary w-100 my-2">See More Replies</span></a>
                            @break
                        @endif

                        @include('comments::_comment', [
                            'comment' => $child,
                            'reply' => true,
                            'grouped_comments' => $grouped_comments,
                        ])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@else
    <div id="comment-{{ $comment->getKey() }}" class="{{ isset($reply) && $reply === true ? 'comment_replies border-left col-12 column mw-100 pr-0' : '' }} pt-4" style="flex-basis: 100%;">
        <div class="media-body row mw-100 mx-0 mb-3" style="flex:1;flex-wrap:wrap;">
            @if (isset($compact) && !$compact)
                <div class="d-none d-md-block">
                    <img class="mr-3 mt-2" src="{{ asset('images/avatars/default.jpg') }}" style="width:70px; height:70px; border-radius:50%;" alt="Default Avatar">
                </div>
            @endif
            <div class="d-block bg-light" style="flex:1">
                <div class="border p-3 rounded">
                    <p>Comment deleted</p>
                    <p class="border-top pt-1 text-right mb-0">
                        <small class="text-muted">{!! $comment->created_at !!}
                            @if ($comment->created_at != $comment->deleted_at)
                                <span class="text-muted border-left mx-1 px-1">(Deleted {!! $comment->deleted_at !!})</span>
                            @endif
                        </small>
                        @if ($comment->type == 'User-User')
                            <a href="{{ url('comment/') . '/' . $comment->id }}"><i class="fas fa-link ml-1" style="opacity: 50%;"></i></a>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Recursion for children --}}
            <div class="w-100 mw-100">
                @if ($grouped_comments->has($comment->getKey()))
                    @foreach ($grouped_comments[$comment->getKey()] as $child)
                        @php $limit++; @endphp

                        @if ($limit >= 3)
                            <a href="{{ url('comment/') . '/' . $comment->id }}"><span class="btn btn-secondary w-100 my-2">See More Replies</span></a>
                            @break
                        @endif

                        @include('comments::_comment', [
                            'comment' => $child,
                            'reply' => true,
                            'grouped_comments' => $grouped_comments,
                        ])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endif
