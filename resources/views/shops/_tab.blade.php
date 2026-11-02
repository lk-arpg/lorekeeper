<div class="card character-bio {{ $loop->last ? '' : 'mb-3' }}">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            @foreach ($stock as $categoryId => $categoryItems)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="categoryTab-{{ isset($categoryItems->first()->category) ? $categoryItems->first()->category->id : 'misc' }}" data-toggle="tab"
                        href="#category-{{ isset($categoryItems->first()->category) ? $categoryItems->first()->category->id : 'misc' }}" role="tab">
                        {!! isset($categoryItems->first()->category) ? $categoryItems->first()->category->name : 'Miscellaneous' !!}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body tab-content">
        @foreach ($stock as $categoryId => $categoryItems)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="category-{{ isset($categoryItems->first()->category) ? $categoryItems->first()->category->id : 'misc' }}">
                @foreach ($categoryItems->chunk(4) as $chunk)
                    <div class="row mb-3">
                        @foreach ($chunk as $item)
                            <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $item->pivot->id }}">
                                @if ($item->has_image)
                                    <div class="mb-1">
                                        <a href="#" class="inventory-stack"><img src="{{ $item->imageUrl }}" alt="{{ $item->name }}" /></a>
                                    </div>
                                @endif
                                <div>
                                    <a href="#" class="inventory-stack inventory-stack-name"><strong>{{ $item->name }}</strong></a>
                                    <div><strong>Cost: </strong> {!! $shop->displayStockCosts($item->pivot->id) ?? 'Free' !!}</div>
                                    @if ($item->pivot->is_limited_stock)
                                        <div>Stock: {{ $item->pivot->quantity }}</div>
                                    @endif
                                    @if ($item->pivot->purchase_limit)
                                        <div class="text-danger">
                                            Max {{ $item->pivot->purchase_limit }}
                                            @if ($item->pivot->purchase_limit_timeframe !== 'lifetime')
                                                {{ $item->pivot->purchase_limit_timeframe }}
                                            @endif per user
                                        </div>
                                    @endif
                                    @if ($item->pivot->disallow_transfer)
                                        <div class="text-danger">Cannot be transferred after purchase</div>
                                    @endif
                                    @if ($item->pivot->is_timed_stock)
                                        <div class="text-info">Available for a limited time!</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
