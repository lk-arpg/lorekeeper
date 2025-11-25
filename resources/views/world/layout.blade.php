@extends('layouts.app')

@section('title')
    World{!! View::hasSection('world-title') ? ' :: ' . trim(View::getSection('world-title')) : '' !!}
@endsection

@section('sidebar')
    @include('world._sidebar')
@endsection

@section('content')
    @yield('world-content')
@endsection

@section('scripts')
    @parent
@endsection
