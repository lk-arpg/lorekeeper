@extends('home.layout')

@section('home-title')
    Trades
@endsection

@section('home-content')
    {!! breadcrumbs(['Trades' => 'trades/open']) !!}

    <h1>
        Trades
    </h1>

    <p>Here are your trades.</p>

    <div class="text-right">
        <a href="{{ url('trades/listings') }}" class="btn btn-dark">View Trade Listings</a>
        <a href="{{ url('trades/listings/create') }}" class="btn btn-secondary">New Trade Listing</a>
        <a href="{{ url('trades/create') }}" class="btn btn-primary">New Trade</a>
    </div>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('trades/proposals*') }}" href="{{ url('trades/proposals') }}">Proposals</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('trades/open*') }}" href="{{ url('trades/open') }}">Open</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('trades/pending*') }}" href="{{ url('trades/pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('trades/completed*') }}" href="{{ url('trades/completed') }}">Completed</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('trades/rejected*') }}" href="{{ url('trades/rejected') }}">Rejected</a>
        </li>
    </ul>

    {!! $trades->render() !!}
    @foreach ($trades as $trade)
        @include('home.trades._trade', ['trade' => $trade])
    @endforeach
    {!! $trades->render() !!}
@endsection
