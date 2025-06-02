@if ($trade)
    @if ($trade->sender_id == Auth::user()->id)
        @if (!$trade->is_sender_confirmed)
            <p>
                This will confirm your offer. @if (!$trade->is_recipient_confirmed)
                    After your trade partner has confirmed their offer, you will be able to confirm the entire trade.
                @endif
            </p>
        @else
            <p>
                This will retract your offer confirmation, allowing you to edit the contents of your trade. @if ($trade->is_sender_trade_confirmed || $trade->is_recipient_trade_confirmed)
                    The trade will need to be confirmed again on both sides after you have reconfirmed your offer.
                @endif
            </p>
        @endif
        {!! Form::open(['url' => 'trades/' . $trade->id . '/confirm-offer']) !!}
        <div class="text-end">
            {!! Form::submit($trade->is_sender_confirmed ? 'Unconfirm' : 'Confirm', ['class' => 'btn btn-' . ($trade->is_sender_confirmed ? 'danger' : 'primary')]) !!}
        </div>
        {!! Form::close() !!}
    @else
        @if (!$trade->is_recipient_confirmed)
            <p>
                This will confirm your offer. @if (!$trade->is_sender_confirmed)
                    After your trade partner has confirmed their offer, you will be able to confirm the entire trade.
                @endif
            </p>
        @else
            <p>
                This will retract your offer confirmation, allowing you to edit the contents of your trade. @if ($trade->is_recipient_trade_confirmed || $trade->is_sender_trade_confirmed)
                    The trade will need to be confirmed again on both sides after you have reconfirmed your offer.
                @endif
            </p>
        @endif
        {!! Form::open(['url' => 'trades/' . $trade->id . '/confirm-offer']) !!}
        <div class="text-end">
            {!! Form::submit($trade->is_recipient_confirmed ? 'Unconfirm' : 'Confirm', ['class' => 'btn btn-' . ($trade->is_recipient_confirmed ? 'danger' : 'primary')]) !!}
        </div>
        {!! Form::close() !!}
    @endif
@else
@endif
