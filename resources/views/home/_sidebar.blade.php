<ul>
    <li class="sidebar-header"><a href="{{ url('/') }}" class="card-link">Home</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Inventory</div>
        <div class="sidebar-item"><a href="{{ url('characters') }}" class="{{ set_active('characters') }}">My Characters</a></div>
        <div class="sidebar-item"><a href="{{ url('characters/myos') }}" class="{{ set_active('characters/myos') }}">My MYO Slots</a></div>
        <div class="sidebar-item"><a href="{{ url('inventory') }}" class="{{ set_active('inventory*') }}">Inventory</a></div>
        <div class="sidebar-item"><a href="{{ url('bank') }}" class="{{ set_active('bank*') }}">Bank</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Activity</div>
        <div class="sidebar-item"><a href="{{ url('submissions') }}" class="{{ set_active('submissions*') }}">Prompt Submissions</a></div>
        <div class="sidebar-item"><a href="{{ url('claims') }}" class="{{ set_active('claims*') }}">Claims</a></div>
        <div class="sidebar-item"><a href="{{ url('characters/transfers/incoming') }}" class="{{ set_active('characters/transfers*') }}">Character Transfers</a></div>
        <div class="sidebar-item"><a href="{{ url('trades/open') }}" class="{{ set_active('trades*') }}">Trades</a></div>
        <div class="sidebar-item"><a href="{{ url('comments/liked') }}" class="{{ set_active('comments/liked*') }}">Liked Comments</a></div>
        @if (config('lorekeeper.extensions.queue_creator.expand_in_user_menu'))
            @php $queues = \App\Models\Queue\Queue::query()->active()->staffOnly(Auth::user())->get(); @endphp
            @foreach ($queues as $queue)
                <div class="sidebar-item"><a href="{{ url('queue-submissions/' . $queue->id) }}" class="{{ set_active('queue-submissions/' . $queue->id) }}">{{ $queue->name }} Submissions</a></div>
            @endforeach
        @else
            <div class="sidebar-item"><a href="{{ url('queue-submissions') }}" class="{{ set_active('queue-submissions*') }}">Queue Submissions</a></div>
        @endif
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Reports</div>
        <div class="sidebar-item"><a href="{{ url('reports') }}" class="{{ set_active('reports*') }}">Reports</a></div>
    </li>
</ul>
