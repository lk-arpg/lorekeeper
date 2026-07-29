<ul>
    <li class="sidebar-header"><a href="{{ url('gallery') }}" class="card-link">Gallery</a></li>

    @auth
        <li class="sidebar-section">
            <div class="sidebar-section-header">My Submissions</div>
            <div class="sidebar-item"><a href="{{ url('gallery/submissions/pending') }}" class="{{ set_active('gallery/submissions*') }}">My Submission Queue</a></div>
            <div class="sidebar-item"><a href="{{ url('user/' . Auth::user()->name . '/gallery') }}" class="{{ set_active('user/' . Auth::user()->name . '/gallery') }}">My Gallery</a></div>
            <div class="sidebar-item"><a href="{{ url('user/' . Auth::user()->name . '/favorites') }}" class="{{ set_active('user/' . Auth::user()->name . '/favorites') }}">My Favorites</a></div>
        </li>
    @endauth

    @if (config('lorekeeper.extensions.show_all_recent_submissions.enable') && config('lorekeeper.extensions.show_all_recent_submissions.links.sidebar'))
        <li class="sidebar-section">
            <div class="sidebar-item"><a href="{{ url('gallery/all') }}" class="{{ set_active('gallery/all') }}">All Recent Submissions</a></div>
        </li>
    @endif

    @if (
        $galleryPage &&
            $sideGallery->children()->visible()->exists())
        <li class="sidebar-section">
            <div class="sidebar-section-header">{{ $sideGallery->name }}: Sub-Galleries</div>
            @foreach ($sideGallery->children()->visible()->get() as $child)
                <div class="sidebar-item"><a href="{{ url('gallery/' . $child->id) }}" class="{{ set_active('gallery/' . $child->id) }}">{{ $child->name }}</a></div>
            @endforeach
        </li>
    @endif

    @if (
        $galleryPage &&
            $sideGallery->siblings() &&
            $sideGallery->siblings()->visible()->exists())
        <li class="sidebar-section">
            <div class="sidebar-section-header">{{ $sideGallery->parent->name }}: Sub-Galleries</div>
            @foreach ($sideGallery->siblings()->visible()->get() as $sibling)
                <div class="sidebar-item"><a href="{{ url('gallery/' . $sibling->id) }}" class="{{ set_active('gallery/' . $sibling->id) }}">{{ $sibling->name }}</a></div>
            @endforeach
        </li>
    @endif

    @if (
        $galleryPage &&
            $sideGallery->avunculi() &&
            $sideGallery->avunculi()->visible()->exists())
        <li class="sidebar-section">
            <div class="sidebar-section-header">{{ $sideGallery->parent->parent->name }}: Sub-Galleries</div>
            @foreach ($sideGallery->avunculi()->visible()->get() as $avunculus)
                <div class="sidebar-item"><a href="{{ url('gallery/' . $avunculus->id) }}" class="{{ set_active('gallery/' . $avunculus->id) }}">{{ $avunculus->name }}</a></div>
            @endforeach
        </li>
    @endif

    <li class="sidebar-section">
        <div class="sidebar-section-header">Galleries</div>
        @foreach ($sidebarGalleries as $gallery)
            <div class="sidebar-item"><a href="{{ url('gallery/' . $gallery->id) }}" class="{{ set_active('gallery/' . $gallery->id) }}">{{ $gallery->name }}</a></div>
        @endforeach
    </li>
</ul>
