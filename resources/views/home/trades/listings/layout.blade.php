@extends('layouts.app')

@section('title')
    Trades ::
    @yield('trade-title')
@endsection

@section('sidebar')
    @include('home.trades.listings._sidebar')
@endsection

@section('content')
    @yield('trade-content')
@endsection

@section('scripts')
    @parent
@endsection
