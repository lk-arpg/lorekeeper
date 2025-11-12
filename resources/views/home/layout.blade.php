@extends('layouts.app')

@section('title')
    Home{!! View::hasSection('home-title') ? ' :: ' . trim(View::getSection('home-title')) : '' !!}
@endsection

@section('sidebar')
    @include('home._sidebar')
@endsection

@section('content')
    @yield('home-content')
@endsection

@section('scripts')
    @parent
@endsection
