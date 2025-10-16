<ul>
    <li class="sidebar-header">
        <a=class="card-link"><a href="{{ url('queues') }}">Queues</a></a>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Queues</div>
        <div class="sidebar-item"><a href="{{ url('queues/queue-categories') }}" class="{{ set_active('queues/queue-categories*') }}">Queue Categories</a></div>
        <div class="sidebar-item"><a href="{{ url('queues/queues') }}" class="{{ set_active('queues/queues*') }}">All Queues</a></div>
    </li>
</ul>
