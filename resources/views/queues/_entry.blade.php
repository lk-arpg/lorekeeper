<div class="row world-entry">
    @if ($category->imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $category->imageUrl }}" data-lightbox="entry" data-title="{{ $category->name }}"><img src="{{ $category->imageUrl }}" class="world-entry-image" alt="{{ $category->name }}" /></a></div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <h3>{!! $category->name !!} @if (isset($searchUrl) && $searchUrl)
                <a href="{{ $searchUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
        </h3>
        <div class="world-entry-text">
            {!! $category->description !!}

            @if (isset($category->limit))
                 <div class="alert alert-info text-center">
                    You can submit to queues within this category {{ $category->limit }} {{ $category->limit > 1 ? 'times' : 'time' }}{{ $category->limit_period ? ' per ' . strtolower($category->limit_period) : '' }}. ( Submitted
                    {{ $category->logCount(Auth::user()) }} /
                    {{ $category->limit }} )
                 </div>
            @endif
            @if (isset($category->limit_concurrent))
                <div class="alert alert-warning text-center">
                    This category does not permit you to submit more submissions to queues within it while you have {{ $category->limit_concurrent }} of them pending or in draft at the same time throughout it.
                </div>
            @endif
        </div>
    </div>
</div>
