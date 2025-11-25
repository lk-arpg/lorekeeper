@extends('layouts.app')

@section('title')
    Design Approvals{!! View::hasSection('design-title') ? ' :: ' . trim(View::getSection('design-title')) : '' !!}
@endsection

@section('sidebar')
    @include('character.design._sidebar')
@endsection

@section('content')
    @yield('design-content')
@endsection

@section('scripts')
    @parent
@endsection
