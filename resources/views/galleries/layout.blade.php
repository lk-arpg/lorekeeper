@extends('layouts.app')

@section('title')
    Gallery{!! View::hasSection('gallery-title') ? ' :: ' . trim(View::getSection('gallery-title')) : '' !!}
@endsection

@section('sidebar')
    @include('galleries._sidebar')
@endsection

@section('content')
    @yield('gallery-content')
@endsection

@section('scripts')
    @parent
@endsection
