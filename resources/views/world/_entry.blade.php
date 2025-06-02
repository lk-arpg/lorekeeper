<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $imageUrl }}" data-lightbox="entry" data-bs-title="{{ $name }}">
                <img src="{{ $imageUrl }}" class="world-entry-image" alt="{{ $name }}" />
            </a>
        </div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        @if (isset($edit))
            <x-admin-edit title="{{ $edit['title'] }}" :object="$edit['object']" />
        @endif
        <h3>
            @if (!$visible)
                <i class="fas fa-eye-slash me-1"></i>
            @endif
            {!! $name !!}
            @if (isset($searchUrl) && $searchUrl)
                <a href="{{ $searchUrl }}" class="world-entry-search text-muted">
                    <i class="fas fa-search"></i>
                </a>
            @endif
        </h3>
        <div class="world-entry-text">
            {!! $description !!}
        </div>
    </div>
</div>
