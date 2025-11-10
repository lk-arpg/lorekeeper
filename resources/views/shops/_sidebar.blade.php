<ul>
    <li class="sidebar-header"><a href="{{ url('shops') }}" class="card-link">Shops</a></li>

    @if (Auth::check())
        <li class="sidebar-section">
            <div class="sidebar-section-header">History</div>
            <div class="sidebar-item"><a href="{{ url('shops/history') }}" class="{{ set_active('shops/history') }}">My Purchase History</a></div>
            <div class="sidebar-section-header">My Currencies</div>
            @foreach (Auth::user()->getCurrencies(true) as $currency)
                <div class="sidebar-item pr-3">{!! $currency->display($currency->quantity) !!}</div>
            @endforeach
        </li>
    @endif

    <li class="sidebar-section">
        <div class="sidebar-section-header">Shops</div>
        @foreach ($shops as $shop)
            @if ($shop->is_staff)
                @if (Auth::check() && Auth::user()->isstaff)
                    <div class="sidebar-item"><a href="{{ $shop->url }}" class="{{ set_active('shops/' . $shop->id) }}">{{ $shop->name }}</a></div>
                @endif
            @else
                <div class="sidebar-item"><a href="{{ $shop->url }}" class="{{ set_active('shops/' . $shop->id) }}">{{ $shop->name }}</a></div>
            @endif
        @endforeach
    </li>
</ul>
