@extends('home.layout')

@section('home-title')
    Trades
@endsection

@section('home-content')
    {!! breadcrumbs(['Trades' => 'trades/open', ($trade ? 'Edit' : 'New') . ' Trade Proposal' => 'trades/proposal']) !!}

    <h1>
        {{ $trade ? 'Edit' : 'New' }} Trade Proposal
    </h1>

    <p>
        Here you can propose a trade to a user. Recipients can modify the proposal as a "counter offer" before accepting or rejecting.
        Note that each person may only add up to <strong>{{ config('lorekeeper.settings.trade_asset_limit') }} things to one proposal.</strong>
    </p>

    {!! Form::open(['url' => 'trades/propose/' . ($trade ? $trade->id : null)]) !!}

    <div class="form-group">
        {!! Form::label('recipient_id', 'Recipient') !!}
        {!! Form::select('recipient_id', $userOptions, $recipient ? $recipient->id : old('recipient_id'), ['class' => 'form-control user-select', 'placeholder' => 'Select User', $trade ? 'disabled' : '']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('This comment will be displayed on the trade index. You can write a helpful note here, for example to note down the purpose of the trade.') !!}
        {!! Form::textarea('comments', $trade ? $trade->comments : null, ['class' => 'form-control', 'rows' => 3]) !!}
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12 col-12">
            @include('home.trades._proposal_offer', [
                'trade' => $trade,
                'user' => Auth::user(),
                'inventory' => $inventory,
                'categories' => $categories,
                'page' => $page,
                'characters' => Auth::user()->allCharacters()->visible()->tradable()->with('designUpdate')->get(),
                'fieldPrefix' => $trade && Auth::user()->id == $trade->recipient_id ? 'recipient_' : null,
            ])
        </div>
        <div class="col-md-6 col-sm-12 col-12" id="recipient">
            @if ($recipient)
                @include('home.trades._proposal_offer', [
                    'trade' => $trade,
                    'user' => $recipient,
                    'inventory' => $recipientInventory,
                    'selectedItems' => $recipientSelectedItems ?? [],
                    'selectedCharacters' => $recipientSelectedCharacters ?? [],
                    'categories' => $categories,
                    'page' => $page,
                    'characters' => $recipient->allCharacters()->visible()->tradable()->with('designUpdate')->get(),
                    'fieldPrefix' => ($trade && $recipient->id == $trade->recipient_id) || !$trade ? 'recipient_' : null,
                ])
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Select a recipient to view their inventory.
                </div>
            @endif
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit(($trade ? 'Edit' : 'Create') . ' Trade Proposal', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@endsection
@section('scripts')
    {{-- this is included here since the js is agnostic to the page --}}
    @include('widgets._bank_select_js')
    @parent
    <script>
        $('.user-select').selectize();

        $('.user-select').change(function() {
            var userId = $(this).val();
            if (!userId) {
                return;
            }

            $.get("{{ url('trades/proposal/user') }}/" + userId, function(data) {
                $('#recipient').html(data);
            });
        });
    </script>
@endsection
