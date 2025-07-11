@extends('home.layout')

@section('home-title')
    Trades
@endsection

@section('home-content')
    {!! breadcrumbs(['Trades' => 'trades/open', 'Trade with ' . $partner->name . ' (#' . $trade->id . ')' => 'trades/' . $trade->id]) !!}

    <h1>
        Trade with {!! $partner->displayName !!} (#{{ $trade->id }})
        <span class="float-right badge badge-{{ $trade->status == 'Pending' || $trade->status == 'Open' || $trade->status == 'Canceled' ? 'secondary' : ($trade->status == 'Completed' ? 'success' : 'danger') }}">{{ $trade->status }}</span>
    </h1>


    <div class="mb-1">
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Sender</h5>
            </div>
            <div class="col-md-10 col-8">{!! $trade->sender->displayName !!}</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Created</h5>
            </div>
            <div class="col-md-10 col-8">{!! format_date($trade->created_at) !!} ({{ $trade->created_at->diffForHumans() }})</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>{{ $trade->status == 'Rejected' || ($trade->status == 'Completed' && $trade->staff_id) ? 'Processed' : 'Last Updated' }}</h5>
            </div>
            <div class="col-md-10 col-8">{!! format_date($trade->updated_at) !!} ({{ $trade->updated_at->diffForHumans() }})</div>
        </div>
        <div>
            <div>
                <h5>Sender's Comments</h5>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    @if ($trade->comments)
                        {!! nl2br(htmlentities($trade->comments)) !!}
                    @else
                        No comment given.
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if ($trade->status == 'Open')
        <div class="alert alert-info">
            <p>
                Please note that to complete a trade, both parties will need to confirm <strong>twice</strong> each.
            </p>
            <p>
                First, after you are done editing your offer, confirm your offer to indicate to your partner that you have finished. Next, after both parties have confirmed, you will receive the option to confirm the entire trade. <i>Please make sure
                    that your partner has attached everything that you are expecting to receive!</i>
            </p>
            <p>
                After both parties have confirmed the entire trade, @if (Settings::get('open_transfers_queue'))
                    if the trade contains a character, it will enter the transfer approval queue. Otherwise,
                @endif the transfers will be processed immediately.
            </p>
        </div>
    @elseif($trade->status == 'Pending')
        <div class="alert alert-warning">This trade is currently in the character transfer approval queue. Please wait for it to be processed.</div>
    @elseif($trade->status == 'Rejected' && $trade->reason)
        <h5 class="text-danger">Staff Comments ({!! $trade->staff->displayName !!})</h5>
        <div class="card border-danger mb-3">
            <div class="card-body">{!! nl2br(htmlentities($trade->reason)) !!}</div>
        </div>
    @endif

    @include('home.trades._offer', ['user' => $trade->sender, 'data' => $senderData, 'trade' => $trade, 'type' => 'sender'])
    @include('home.trades._offer', ['user' => $trade->recipient, 'data' => $recipientData, 'trade' => $trade, 'type' => 'recipient'])

    @if ((Auth::user()->id == $trade->sender_id || Auth::user()->id == $trade->recipient_id) && $trade->status == 'Open')
        <div class="text-right">
            @if (!$trade->isConfirmable)
                {!! add_help('Both parties must confirm their offers before you can confirm this trade.') !!}
                <a href="#" class="btn btn-outline-primary disabled">Confirm Trade</a>
            @else
                @if (Auth::user()->id == $trade->sender_id)
                    @if (!$trade->is_sender_trade_confirmed)
                        <a href="#" class="btn btn-outline-primary" id="confirmTradeButton">Confirm Trade</a>
                    @endif
                @elseif(Auth::user()->id == $trade->recipient_id)
                    @if (!$trade->is_recipient_trade_confirmed)
                        <a href="#" class="btn btn-outline-primary" id="confirmTradeButton">Confirm Trade</a>
                    @endif
                @endif
            @endif
            <a href="#" class="btn btn-outline-danger" id="cancelTradeButton">Cancel Trade</a>
        </div>
    @endif

@endsection

@section('scripts')
    @include('widgets._inventory_select_js')
    <script>
        $(document).ready(function() {
            $('#confirmOfferButton').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('trades/' . $trade->id . '/confirm-offer') }}",
                    '{{ (Auth::user()->id == $trade->sender_id ? ($trade->is_sender_confirmed ? 'Unconfirm' : 'Confirm') : ($trade->is_recipient_confirmed ? 'Unconfirm' : 'Confirm')) . ' Offer' }}');
            });
            $('#confirmTradeButton').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('trades/' . $trade->id . '/confirm-trade') }}",
                    '{{ (Auth::user()->id == $trade->sender_id ? ($trade->is_sender_trade_confirmed ? 'Unconfirm' : 'Confirm') : ($trade->is_recipient_trade_confirmed ? 'Unconfirm' : 'Confirm')) . ' Trade' }}');
            });
            $('#cancelTradeButton').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('trades/' . $trade->id . '/cancel-trade') }}", 'Cancel Trade');
            });
        });
    </script>
@endsection
