<div class="row world-entry">
    @if ($queue->has_image)
        <div class="col-md-3 world-entry-image"><a href="{{ $queue->imageUrl }}" data-lightbox="entry" data-title="{{ $queue->name }}"><img src="{{ $queue->imageUrl }}" class="world-entry-image" alt="{{ $queue->name }}" /></a></div>
    @endif
    <div class="{{ $queue->has_image ? 'col-md-9' : 'col-12' }}">
        @if (Auth::check() && Auth::user()->hasPower('edit_data'))
            <a data-toggle="tooltip" title="Edit Queue" href="{{ $queue->adminUrl }}" class="mb-2 float-right">
                <h3><i class="fas fa-pencil-alt"></i></h3>
            </a>
        @endif
        <div class="mb-3">
            @if (isset($isPage))
                <h1 class="mb-0">{!! $queue->name !!} <a href="{{ $queue->idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a></h1>
            @else
                <h2 class="mb-0"><a href="{{ $queue->idUrl }}">{!! $queue->name !!}</a></h2>
            @endif
            @if ($queue->queue_category_id)
                <div><strong>Category: </strong>{!! $queue->category->displayName !!}</div>
            @endif
            @if ($queue->start_at && $queue->start_at->isFuture())
                <div><strong>Starts: </strong>{!! format_date($queue->start_at) !!} ({{ $queue->start_at->diffForHumans() }})</div>
            @endif
            @if ($queue->end_at)
                <div><strong>Ends: </strong>{!! format_date($queue->end_at) !!} ({{ $queue->end_at->diffForHumans() }})</div>
            @endif
        </div>
        <div class="world-entry-text">
            <p>{{ $queue->summary }}</p>
            <h3 class="mb-3"><a data-toggle="collapse" href="#queue-{{ $queue->id }}" @if (isset($isPage)) aria-expanded="true" @endif>Details <i class="fas fa-angle-down"></i></a></h3>
            <div class="collapse @if (isset($isPage)) show @endif" id="queue-{{ $queue->id }}">
                @if ($queue->parsed_description)
                    {!! $queue->parsed_description !!}
                @else
                    <p>No further details.</p>
                @endif
                @if ($queue->hide_submissions == 1 && isset($queue->end_at) && $queue->end_at > Carbon\Carbon::now())
                    <p class="text-info">Submissions to this queue are hidden until this queue ends.</p>
                @elseif($queue->hide_submissions == 2)
                    <p class="text-info">Submissions to this queue are hidden.</p>
                @endif
                <h3>Default Rewards</h3>
                @include('queues._queue_rewards')
                @include('queues._queue_limits', ['staff' => false, 'user' => Auth::user()])
            </div>
        </div>
        @if (Auth::check())
            <div class="text-right">
                @if ($queue->checkConcurrentSubmissionLimit(Auth::user()) && $queue->checkSubmissionLimit(Auth::user()))
                    @if ($queue->end_at && $queue->end_at->isPast())
                        <span class="text-secondary">This queue has ended.</span>
                    @elseif($queue->start_at && $queue->start_at->isFuture())
                        <span class="text-secondary">This queue is not open for submissions yet.</span>
                    @else
                        <a href="{{ url('queue-submissions/new/' . $queue->id) }}" class="btn btn-primary">Submit Queue</a>
                    @endunless
                @else
                    <div class="alert alert-warning text-center">
                        You have reached the submission cap or are not logged in.
                    </div>
                @endif
        </div>
    @endif
</div>
</div>
