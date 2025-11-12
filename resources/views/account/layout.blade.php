@extends('layouts.app')

@section('title')
    Account{!! View::hasSection('account-title') ? ' :: ' . trim(View::getSection('account-title')) : '' !!}
@endsection

@section('sidebar')
    @include('account._sidebar')
@endsection

@section('content')
    @yield('account-content')
@endsection
