<div class="card">
    <div class="card-body">
        @if (isset($staffView) && $staffView)
            <p>This user has completed this prompt <strong>{{ $count }}</strong> time{{ $count == 1 ? '' : 's' }}.</p>
            @include('queues._queue_limits', ['staff' => true, 'user' => $submission->user])
        @else
            <p>You have completed this queue <strong>{{ $count }}</strong> time{{ $count == 1 ? '' : 's' }}.</p>
            @include('queues._queue_limits', ['staff' => false, 'user' => Auth::user()])
        @endif
        <h3>Default Rewards</h3>
        @include('queues._queue_rewards')
        <hr>
        @if (View::exists('home.queues.types.' . $queue->queue_type))
            @include('home.queues.types.' . $queue->queue_type, ['data' => isset($submission->data['queue']) ? $submission->data['queue'] : null])
        @else
            <p>This queue has no associated extra form to fill in.</p>
        @endif
    </div>
</div>
