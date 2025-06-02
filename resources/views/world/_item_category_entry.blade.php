<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $imageUrl }}" data-lightbox="entry" data-bs-title="{{ $name }}">
                <img src="{{ $imageUrl }}" class="world-entry-image" alt="{{ $name }}" />
            </a>
        </div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Category" :object="$category" />
        <h3>
            @if (!$category->is_visible)
                <i class="fas fa-eye-slash me-1"></i>
            @endif
            {!! $name !!}
            @if (isset($searchUrl) && $searchUrl)
                <a href="{{ $searchUrl }}" class="world-entry-search text-muted">
                    <i class="fas fa-search"></i>
                </a>
            @endif
        </h3>
        @if ($category->is_character_owned == 1)
            <div>
                <strong>Characters can own {{ $category->character_limit != 0 ? $category->character_limit : '' }} items in this category{{ $category->can_name != 0 ? ', as well as name them' : '' }}!</strong>
            </div>
        @endif
        <div class="world-entry-text">
            {!! $description !!}
        </div>
    </div>
</div>
