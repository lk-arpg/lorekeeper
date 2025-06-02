@if ($trade)
    @if ($trade->sender_id == Auth::user()->id)
        @if (!$trade->is_sender_trade_confirmed)
            <p>
                This will confirm your agreement to this trade. @if (!$trade->is_recipient_trade_confirmed)
                    After your trade partner has also confirmed the trade, you will not be able to further edit the contents of this trade.
                @else
                    You will not be able to further edit the contents of this trade.
                @endif
            </p>
            {!! Form::open(['url' => 'trades/' . $trade->id . '/confirm-trade']) !!}
            <div class="text-end">
                {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @else
            <p>
                You have already confirmed your agreement to this trade. @if (!$trade->is_recipient_trade_confirmed)
                    Please wait for {!! $trade->recipient->displayName !!} to confirm the trade.
                @endif
            </p>
        @endif
    @else
        @if (!$trade->is_recipient_trade_confirmed)
            <p>
                This will confirm your agreement to this trade. @if (!$trade->is_recipient_trade_confirmed)
                    After your trade partner has also confirmed the trade, you will not be able to further edit the contents of this trade.
                @else
                    You will not be able to further edit the contents of this trade.
                @endif
            </p>
            {!! Form::open(['url' => 'trades/' . $trade->id . '/confirm-trade']) !!}
            <div class="text-end">
                {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @else
            <p>
                You have already confirmed your agreement to this trade. @if (!$trade->is_sender_trade_confirmed)
                    Please wait for {!! $trade->sender->displayName !!} to confirm the trade.
                @endif
            </p>
        @endif
    @endif
@else
@endif
