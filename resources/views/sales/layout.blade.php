@extends('layouts.app')

@section('title')
    Site Sales{!! View::hasSection('sales-title') ? ' :: ' . trim(View::getSection('sales-title')) : '' !!}
@endsection

@section('sidebar')
    @include('sales._sidebar')
@endsection

@section('content')
    @yield('sales-content')
@endsection
