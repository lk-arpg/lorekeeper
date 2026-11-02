@extends('home.layout')

@section('home-title')
    Trades
@endsection

@section('home-content')
    {!! breadcrumbs(['Trades' => 'trades/open', 'New Trade' => 'trades/create']) !!}

    <h1>
        New Trade
    </h1>

    <p>
        Create a new trade. You can modify the trade attachments after trade creation - this only sets up the trade, so you don't have to worry about having everything in place at the start. The recipient will be notified of the new trade and will be
        able to edit their attachments as well. Note that each person may only add up to <strong>{{ config('lorekeeper.settings.trade_asset_limit') }} things to one trade - if necessary, please create a new trade to add more.</strong>
    </p>

    {!! Form::open(['url' => 'trades/create']) !!}

    <div class="form-group">
        {!! Form::label('recipient_id', 'Recipient') !!}
        {!! Form::select('recipient_id', $userOptions, old('recipient_id'), ['class' => 'form-control user-select', 'placeholder' => 'Select User']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('terms_link', 'Proof of Terms (URL)') !!}
        {!! add_help('Enter a link to proof of the terms of agreement for the trade. This field is not strictly required but is strongly recommended.') !!}
        {!! Form::text('terms_link', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('This comment will be displayed on the trade index. You can write a helpful note here, for example to note down the purpose of the trade.') !!}
        {!! Form::textarea('comments', null, ['class' => 'form-control', 'rows' => 3]) !!}
    </div>
    @include('widgets._inventory_select', [
        'user' => Auth::user(),
        'inventory' => $inventory,
        'categories' => $categories,
        'selected' => [],
        'page' => $page,
    ])
    @include('widgets._user_character_select', ['readOnly' => true, 'categories' => $characterCategories])
    @include('widgets._bank_select', ['owner' => Auth::user(), 'selected' => null, 'isTransferrable' => true])
    <div class="text-right">{!! Form::submit('Create Trade', ['class' => 'btn btn-primary']) !!}</div>
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
