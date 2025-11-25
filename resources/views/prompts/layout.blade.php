@extends('layouts.app')

@section('title')
    Prompts{!! View::hasSection('prompts-title') ? ' :: ' . trim(View::getSection('prompts-title')) : '' !!}
@endsection

@section('sidebar')
    @include('prompts._sidebar')
@endsection

@section('content')
    @yield('prompts-content')
@endsection

@section('scripts')
    @parent
@endsection
