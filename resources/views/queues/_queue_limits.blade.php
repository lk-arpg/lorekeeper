@if (Auth::check())
    @if ($queue->queue_category_id && isset($queue->category->limit))
        <div class="alert alert-info text-center">
            {{ $staff ? 'This user' : 'You' }} can submit to queues within this queue's category ( {!! $queue->category->displayName !!} ) {{ $queue->category->limit }}
            {{ $queue->category->limit > 1 ? 'times' : 'time' }}{{ $queue->category->limit_period ? ' per ' . strtolower($queue->category->limit_period) : '' }}. ( Submitted
            {{ $queue->category->logCount($user) }} /
            {{ $queue->category->limit }} )
        </div>
    @elseif (isset($queue->limit))
        <div class="alert alert-info text-center">
            {{ $staff ? 'This user' : 'You' }} can submit to this queue {{ $queue->limit }} {{ $queue->limit > 1 ? 'times' : 'time' }}{{ $queue->limit_period ? ' per ' . strtolower($queue->limit_period) : '' }}. ( Submitted
            {{ $queue->logCount($user) }} /
            {{ $queue->limit }} )
        </div>
    @endif
    @if ($queue->queue_category_id && isset($queue->category->limit_concurrent))
        <div class="alert alert-warning text-center">
            This queue's category ( {!! $queue->category->displayName !!} ) does not permit {{ $staff ? 'this user' : 'you' }} to submit more submissions to queues within it while {{ $staff ? 'this user has' : 'you have' }}
            {{ $queue->category->limit_concurrent }} of them pending or in
            draft at the same time throughout it.
        </div>
    @elseif (isset($queue->limit_concurrent))
        <div class="alert alert-warning text-center">
            This queue does not permit {{ $staff ? 'this user' : 'you' }} to submit more submissions to it while {{ $staff ? 'this user has' : 'you have' }} {{ $queue->limit_concurrent }} of them pending or in draft at the same time.
        </div>
    @endif
@endif
