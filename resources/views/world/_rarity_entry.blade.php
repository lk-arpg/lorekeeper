<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image">
            <a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}">
                <img src="{{ $imageUrl }}" class="world-entry-image" alt="{{ $name }}" />
            </a>
        </div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="{{ $edit['title'] }}" :object="$edit['object']" />
        <h3>
            {!! $name !!}
            <div class="float-right small">
                @if (isset($searchItemsUrl) && $searchItemsUrl)
                    <a href="{{ $searchItemsUrl }}" class="world-entry-search text-muted small"><i class="fas fa-search"></i> Items</a>
                @endif
                @if (isset($searchFeaturesUrl) && $searchFeaturesUrl)
                    <a href="{{ $searchFeaturesUrl }}" class="world-entry-search text-muted small ml-3"><i class="fas fa-search"></i> Traits</a>
                @endif
                @if (isset($searchCharactersUrl) && $searchCharactersUrl)
                    <a href="{{ $searchCharactersUrl }}" class="world-entry-search text-muted small ml-3 mr-2"><i class="fas fa-search"></i> Characters</a>
                @endif
            </div>

        </h3>
        <div class="world-entry-text">
            {!! $description !!}
        </div>
    </div>
</div>
