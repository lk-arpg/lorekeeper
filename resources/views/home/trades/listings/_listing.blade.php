<div class="card mb-3">
    <div class="card-header">
        <h3 class="mb-0">
            {!! $listing->displayName !!}: Posted by {!! $listing->user->displayName !!}
            <a class="float-right" href="{{ url('reports/new?url=') . $listing->url }}">
                <i class="fas fa-exclamation-triangle" data-toggle="tooltip" title="Click here to report this trade listing." style="font-size: 75%; opacity: 50%;"></i>
            </a>
            @if (isset($showStatus) && $showStatus)
                <div class="mr-2 badge badge-{{ !$listing->isActive ? 'danger' : 'success' }} float-right">
                    {{ !$listing->isActive ? 'Expired' : 'Active' }}
                </div>
            @endif
        </h3>
    </div>
    <div class="card-body">
        @if (isset($trade->terms_link) && $trade->terms_link)
            <div class="row">
                <div class="col-md-2 col-4">
                    <h5>Proof of Terms</h5>
                </div>
                <div class="col-md-10 col-8"><a href="{{ $trade->terms_link }}">{{ $trade->terms_link }}</a></div>
            </div>
        @endif
        @if ($listing->comments)
            <div class="mb-2">
                {!! nl2br(htmlentities($listing->comments)) !!}
            </div>
        @endif
        <strong>Contact Via:</strong> {!! nl2br(htmlentities($listing->contact)) !!}
        <hr />
        <div class="row">
            <div class="col-md-6">
                <h5 class="card-heading">
                    Seeking:
                </h5>
                @include('home.trades.listings._seeking_summary', [
                    'user' => $listing->user,
                    'data' => isset($listing->data['seeking']) ? parseAssetData($listing->data['seeking']) : null,
                    'listing' => $listing,
                    'etc' => isset($listing->data['seeking_etc']) ? $listing->data['seeking_etc'] : null,
                ])
            </div>
            <div class="col-md-6">
                <h5 class="card-heading">
                    Offering:
                </h5>
                @include('home.trades.listings._offer_summary', [
                    'user' => $listing->user,
                    'data' => isset($listing->data['offering']) ? parseAssetData($listing->data['offering']) : null,
                    'listing' => $listing,
                    'etc' => isset($listing->data['offering_etc']) ? $listing->data['offering_etc'] : null,
                ])
            </div>
        </div>
        <hr />
        @php
            $commentCount = App\Models\Comment\Comment::where('commentable_type', 'App\Models\Trade\TradeListing')->where('commentable_id', $listing->id)->count();
        @endphp
        <div class="text-right">
            @if (Auth::check() && Auth::user()->id != $listing->user_id)
                <a href="{{ url('trades/proposal?recipient_id=' . $listing->user_id . '&trade_listing_id=' . $listing->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-handshake"></i>
                    Propose Trade
                </a>
            @endif
            <a href="{{ $listing->url }}" class="btn btn-outline-primary">
                <i class="fas fa-comment"></i>
                {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : '' }} ・ View Details
            </a>
        </div>
    </div>
</div>
