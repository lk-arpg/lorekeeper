@extends('layouts.app')

@section('title')
    Admin{!! View::hasSection('admin-title') ? ' :: ' . trim(View::getSection('admin-title')) : '' !!}
@endsection

@section('sidebar')
    @include('admin._sidebar')
@endsection

@section('content')
    @yield('admin-content')
@endsection

@section('scripts')
    @parent
@endsection
