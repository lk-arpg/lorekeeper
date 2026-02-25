<div class="row world-entry">
    @if ($category->categoryImageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $category->categoryImageUrl }}" data-lightbox="entry" data-title="{{ $category->displayName }}"><img src="{{ $category->categoryImageUrl }}" class="world-entry-image"
                    alt="{{ $category->displayName }}" /></a></div>
    @endif
    <div class="{{ $category->categoryImageUrl ? 'col-md-9' : 'col-12' }}">
        @if (Auth::check() && Auth::user()->hasPower('edit_data'))
            <a data-toggle="tooltip" title="Edit Category" href="{{ $category->adminUrl }}" class="mb-2 float-right">
                <h3><i class="fas fa-pencil-alt"></i></h3>
            </a>
        @endif
        <h3>{!! $category->displayName !!}
        </h3>
        <div class="world-entry-text">
            {!! $category->parsed_description !!}

            @if (isset($category->limit))
                <div class="alert alert-info text-center">
                    You can submit to queues within this category {{ $category->limit }} {{ $category->limit > 1 ? 'times' : 'time' }}{{ $category->limit_period ? ' per ' . strtolower($category->limit_period) : '' }}.
                    @if (Auth::check())
                        (Submitted
                        {{ $category->logCount(Auth::user()) ?? 0 }} /
                        {{ $category->limit }})
                    @endif
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
