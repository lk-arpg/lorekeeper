<div>
    <div class="d-flex mw-100 row mx-0" style="overflow:hidden;">
        @php
            $comments = isset($sort) && $sort == 'oldest' ? $comments->sortBy('created_at') : $comments->sortByDesc('created_at');

            if (isset($perPage)) {
                $page = request()->query('page', 1) - 1;

                $parentComments = $comments->where('child_id', null);
                $slicedParentComments = $parentComments->slice($page * $perPage, $perPage);

                $m = config('comments.model'); // This has to be done like this, otherwise it will complain.
                $modelKeyName = (new $m())->getKeyName(); // This defaults to 'id' if not changed.

                $slicedParentCommentsIds = $slicedParentComments->pluck($modelKeyName)->toArray();

                // Remove parent Comments from comments.
                $comments = $comments->where('child_id', '!=', null);

                $page = request()->query('page', 1);
                $grouped_comments = new \Illuminate\Pagination\LengthAwarePaginator($slicedParentComments->merge($comments)->groupBy('child_id'), $parentComments->count(), $perPage, $page, [
                    'path' => isset($url) ? $url : request()->url(),
                    'query' => [
                        'sort' => isset($sort) ? $sort : 'newest',
                        'perPage' => $perPage,
                    ],
                ]);
            } else {
                $grouped_comments = $comments->groupBy('child_id');
            }
        @endphp
        @foreach ($grouped_comments as $comment_id => $comments)
            {{-- Process parent nodes --}}
            @if ($comment_id == '')
                @foreach ($comments as $comment)
                    @include('comments::_comment', [
                        'comment' => $comment,
                        'grouped_comments' => $grouped_comments,
                        'limit' => 0,
                        'compact' => $comment->type == 'Staff-Staff' ? true : false,
                        'allow_dislikes' => isset($allow_dislikes) ? $allow_dislikes : false,
                    ])
                @endforeach
            @endif
        @endforeach
    </div>

    @if ($comments->count() < 1)
        <div class="alert alert-warning">There are no comments yet.</div>
    @endif

    @isset($perPage)
        <div class="ml-auto mt-2">{{ $grouped_comments->links() }}</div>
    @endisset
</div>
