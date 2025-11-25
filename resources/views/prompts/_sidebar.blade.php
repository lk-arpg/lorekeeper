<ul>
    <li class="sidebar-header">
        <a href="{{ url('prompts') }}" class="card-link">Prompts</a>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Prompts</div>
        <div class="sidebar-item"><a href="{{ url('prompts/prompt-categories') }}" class="{{ set_active('prompts/prompt-categories*') }}">Prompt Categories</a></div>
        <div class="sidebar-item"><a href="{{ url('prompts/prompts') }}" class="{{ set_active('prompts/prompts*') }}">All Prompts</a></div>
    </li>
</ul>
