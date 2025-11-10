@extends('home.layout')

@section('home-title')
    Trades
@endsection

@section('home-content')
    {!! breadcrumbs(['Trades' => 'trades/open', 'Trade with ' . $partner->name . ' (#' . $trade->id . ')' => 'trades/' . $trade->id, 'Edit Trade' => 'trades/' . $trade->id . '/edit']) !!}

    <h1>
        Edit Trade
    </h1>

    <p>
        Edit the contents of this trade freely. Your trade partner will only be notified once you have confirmed your offer. Note that each person may only add up to <strong>{{ config('lorekeeper.settings.trade_asset_limit') }} things to one trade
            - if necessary, please create a new trade to add more.</strong>
    </p>

    {!! Form::open(['url' => 'trades/' . $trade->id . '/edit']) !!}

    @if (Auth::user()->id == $trade->sender_id)
        <div class="form-group">
            {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('This comment will be displayed on the trade index. You can write a helpful note here, for example to note down the purpose of the trade.') !!}
            {!! Form::textarea('comments', $trade->comments, ['class' => 'form-control']) !!}
        </div>
    @endif
    @include('widgets._inventory_select', ['user' => Auth::user(), 'inventory' => $inventory, 'categories' => $categories, 'selected' => $trade->getInventory(Auth::user()), 'page' => $page])
    @include('widgets._user_character_select', ['readOnly' => true, 'categories' => $characterCategories, 'selected' => $trade->getCharacters(Auth::user())])
    @include('widgets._bank_select', ['owner' => Auth::user(), 'selected' => $trade->getCurrencies(Auth::user()), 'isTransferrable' => true])
    <div class="text-right">{!! Form::submit('Edit Trade', ['class' => 'btn btn-primary']) !!}</div>
    {!! Form::close() !!}
@endsection
@section('scripts')
    @parent
    @include('widgets._bank_select_row', ['owners' => [Auth::user()], 'isTransferrable' => true])
    @include('widgets._bank_select_js', [])
    @include('widgets._inventory_select_js', ['readOnly' => true])
    @include('widgets._user_character_select_js', ['readOnly' => true])
    <script>
        $('.user-select').selectize();
    </script>
@endsection
