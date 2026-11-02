<div class="col-md-3 col-6 mb-3 text-center">
    @if ($shop->has_image)
        <div class="shop-image">
            <a href="{{ $shop->url }}"><img src="{{ $shop->shopImageUrl }}" alt="{{ $shop->name }}" /></a>
        </div>
    @endif
    <div class="shop-name mt-1">
        <a href="{{ $shop->url }}" class="h5 mb-0">
            {!! $shop->is_staff ? '<i class="fas fa-crown mr-1"></i>' : '' !!}
            {{ $shop->name }}
        </a>
        @include('widgets._limits', ['object' => $shop, 'compact' => true])
        @if ($shop->is_fto)
            <span class="badge badge-pill badge-success">FTO Shop</span>
        @endif
    </div>
</div>
