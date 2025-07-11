<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" alt="{{ $name }}" /></a></div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Item" :object="$item" />
        <h3>
            @if (!$item->is_released)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            {!! $name !!}
            @if (isset($idUrl) && $idUrl)
                <a href="{{ $idUrl }}" class="world-entry-search text-muted">
                    <i class="fas fa-search"></i>
                </a>
            @endif
        </h3>
        <div class="row">
            @if (isset($item->category) && $item->category)
                <div class="col-md">
                    <p>
                        <strong>Category:</strong>
                        @if (!$item->category->is_visible)
                            <i class="fas fa-eye-slash mx-1 text-danger"></i>
                        @endif
                        <a href="{!! $item->category->url !!}">
                            {!! $item->category->name !!}
                        </a>
                    </p>
                </div>
            @endif
            @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                @if (isset($item->rarity) && $item->rarity)
                    <div class="col-md">
                        <p><strong>Rarity:</strong> {!! $item->rarity->displayName !!}</p>
                    </div>
                @endif
                @if (isset($item->itemArtist) && $item->itemArtist)
                    <div class="col-md">
                        <p><strong>Artist:</strong> {!! $item->itemArtist !!}</p>
                    </div>
                @endif
            @endif
            @if (isset($item->data['resell']) && $item->data['resell'] && App\Models\Currency\Currency::where('id', $item->resell->flip()->pop())->first() && config('lorekeeper.extensions.item_entry_expansion.resale_function'))
                <div class="col-md">
                    <p><strong>Resale Value:</strong> {!! App\Models\Currency\Currency::find($item->resell->flip()->pop())->display($item->resell->pop()) !!}</p>
                </div>
            @endif
            <div class="col-md-6 col-md">
                <div class="row">
                    @foreach ($item->tags as $tag)
                        @if ($tag->is_active)
                            <div class="col">
                                {!! $tag->displayTag !!}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="world-entry-text">
            @if (isset($item->reference) && $item->reference && config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                <p>
                    <strong>Reference Link:</strong>
                    <a href="{{ $item->reference }}">
                        {{ $item->reference }}
                    </a>
                </p>
            @endif
            {!! $description !!}
            @if (((isset($item->uses) && $item->uses) || (isset($item->source) && $item->source) || $item->shop_stock_count || (isset($item->data['prompts']) && $item->data['prompts'])) && config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                <div class="text-right">
                    <a data-toggle="collapse" href="#item-{{ $item->id }}" class="text-primary">
                        <strong>Show details...</strong>
                    </a>
                </div>
                <div class="collapse" id="item-{{ $item->id }}">
                    @if (isset($item->uses) && $item->uses)
                        <p>
                            <strong>Uses:</strong> {{ $item->uses }}
                        </p>
                    @endif
                    @if ((isset($item->source) && $item->source) || $item->shop_stock_count || (isset($item->data['prompts']) && $item->data['prompts']))
                        <h5>Availability</h5>
                        <div class="row">
                            @if (isset($item->source) && $item->source)
                                <div class="col">
                                    <p>
                                        <strong>Source:</strong>
                                    </p>
                                    <p>
                                        {!! $item->source !!}
                                    </p>
                                </div>
                            @endif
                            @if ($item->shop_stock_count)
                                <div class="col">
                                    <p>
                                        <strong>Purchaseable At:</strong>
                                    </p>
                                    <div class="row">
                                        @foreach ($item->shops(Auth::user() ?? null) as $shop)
                                            <div class="col">
                                                <a href="{{ $shop->url }}">
                                                    {{ $shop->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if (isset($item->data['prompts']) && $item->data['prompts'])
                                <div class="col">
                                    <p>
                                        <strong>Drops From:</strong>
                                    </p>
                                    <div class="row">
                                        @foreach ($item->prompts as $prompt)
                                            <div class="col">
                                                <a href="{{ $prompt->url }}">
                                                    {{ $prompt->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
