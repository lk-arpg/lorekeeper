<div class="card">
    <div class="card-body">
        @if (isset($staffView) && $staffView)
            <p>This user has completed this prompt <strong>{{ $count }}</strong> time{{ $count == 1 ? '' : 's' }}.</p>
            @if (isset($queue->limit))
                <p class="text-right">
                    Players can submit to this queue {{ $queue->limit }} {{ $queue->limit > 1 ? 'times' : 'time' }}{{ $queue->limit_period ? ' per ' . strtolower($queue->limit_period) : '' }}. ( Submitted {{ $queue->logCount($user) }} /
                    {{ $queue->limit }} )
                </p>
            @endif
        @else
            <p>You have completed this queue <strong>{{ $count }}</strong> time{{ $count == 1 ? '' : 's' }}.</p>
            @if (isset($queue->limit))
                <p class="text-right">
                    You can submit to this queue {{ $queue->limit }} {{ $queue->limit > 1 ? 'times' : 'time' }}{{ $queue->limit_period ? ' per ' . strtolower($queue->limit_period) : '' }}. ( Submitted {{ $queue->logCount($user) }} /
                    {{ $queue->limit }} )
                </p>
            @endif
        @endif
        <hr>
        @if (View::exists('home.queues.types.' . $queue->queue_type))
            @include('home.queues.types.' . $queue->queue_type, ['data' => isset($submission->data['queue']) ? $submission->data['queue'] : null])
        @else
            <p>This queue has no associated extra form to fill in.</p>
        @endif
    </div>
</div>
