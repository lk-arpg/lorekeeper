@extends('layouts.app')

@section('title')
    Queues ::
    @yield('queues-title')
@endsection

@section('sidebar')
    @include('queues._sidebar')
@endsection

@section('content')
    @yield('queues-content')
@endsection

@section('scripts')
    @parent
@endsection
