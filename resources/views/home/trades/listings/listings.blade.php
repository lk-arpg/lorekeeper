@extends('home.trades.listings.layout')

@section('trade-title')
    Expired Listings
@endsection

@section('trade-content')
    {!! breadcrumbs(['Trades' => 'trades/open', 'Listings' => 'trades/listings', 'Expired' => 'trades/listings/expired']) !!}

    <h1>
        My Trade Listings
    </h1>

    <p>Here are all of your trade listings. Listings are active for {{ $listingDuration }} days before they expire, after which they can be viewed via their permalink.</p>

    {!! $listings->render() !!}
    @foreach ($listings as $listing)
        @include('home.trades.listings._listing', [
            'listing' => $listing,
            'showStatus' => true,
        ])
    @endforeach
    {!! $listings->render() !!}
@endsection
