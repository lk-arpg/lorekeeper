<div class="card mb-3">
    <div class="card-header">
        <h2 class="mb-0"><span
                class="float-right badge badge-{{ $trade->status == 'Pending' || $trade->status == 'Open' || $trade->status == 'Canceled' ? 'secondary' : ($trade->status == 'Completed' ? 'success' : 'danger') }}">{{ $trade->status }}</span><a
                href="{{ $trade->url }} ">Trade (#{{ $trade->id }})</a></h2>
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
        @if ($trade->comment)
            <div>{!! nl2br(htmlentities($trade->comment)) !!}</div>
        @endif
        <div class="row">
            <div class="col-md-6">
                <h3 class="card-heading">{!! $trade->sender->id == Auth::user()->id ? 'Your Offer' : $trade->sender->displayName . '\'s Offer' !!}</h3>
                @include('home.trades._offer_summary', ['user' => $trade->sender, 'data' => isset($trade->data['sender']) ? parseAssetData($trade->data['sender']) : null, 'trade' => $trade])
            </div>
            <div class="col-md-6">
                <h3 class="card-heading">{!! $trade->recipient->id == Auth::user()->id ? 'Your Offer' : $trade->recipient->displayName . '\'s Offer' !!}</h3>
                @include('home.trades._offer_summary', ['user' => $trade->recipient, 'data' => isset($trade->data['recipient']) ? parseAssetData($trade->data['recipient']) : null, 'trade' => $trade])
            </div>
        </div>
        <hr />
        <div class="text-right">
            <a href="{{ $trade->url }}" class="btn btn-outline-primary">View Details</a>
            @if ($trade->status == 'Pending')
                @if (!$trade->is_approved)
                    <div class="text-right">
                        <a href="#" class="btn btn-outline-success trade-action-button" data-id="{{ $trade->id }}" data-action="approve">Approve</a>
                        <a href="#" class="btn btn-outline-danger trade-action-button" data-id="{{ $trade->id }}" data-action="reject">Reject</a>
                    </div>
                @else
                    Currently awaiting staff approval
                @endif
            @endif
        </div>
    </div>
</div>
