@extends('layouts.app')

@section('title')
    Site News{!! View::hasSection('news-title') ? ' :: ' . trim(View::getSection('news-title')) : '' !!}
@endsection

@section('sidebar')
    @include('news._sidebar')
@endsection

@section('content')
    @yield('news-content')
@endsection
