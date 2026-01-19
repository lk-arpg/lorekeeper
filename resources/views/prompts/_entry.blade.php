<div class="row world-entry">
    @if ($category->imageUrl)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $category->imageUrl }}" data-lightbox="entry" data-title="{{ $category->name }}"><img src="{{ $category->imageUrl }}" class="world-entry-image" alt="{{ $category->name }}" /></a>
        </div>
    @endif
    <div class="{{ $category->imageUrl ? 'col-md-9' : 'col-12' }}">
        @if (isset($category->edit))
            <x-admin-edit title="{{ $category->edit['title'] }}" :object="$category->edit['object']" />
        @endif
        <h3>
            {!! $category->name !!}
            @if ($category->parent_id)
                <span class="text-muted"> (in {!! $category->parent->displayName !!})</span>
            @endif
            @if (isset($category->searchUrl) && $category->searchUrl)
                <a href="{{ $category->searchUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
        </h3>
        <div class="world-entry-text">
            @if ($category->children->count())
                <div class="mb-2">
                    <strong>Subcategories:</strong>
                    @foreach ($category->children as $child)
                        <a href="{{ $child->url }}">{{ $child->name }}</a>{{ !$loop->last ? ',' : '' }}
                    @endforeach
                </div>
            @endif
            {!! $category->description !!}
        </div>
    </div>
</div>
