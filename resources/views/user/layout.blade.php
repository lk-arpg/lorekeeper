@extends('layouts.app')

@section('title')
    User{!! View::hasSection('profile-title') ? ' :: ' . trim(View::getSection('profile-title')) : '' !!}
@endsection

@section('sidebar')
    @if (isset($user) && $user->is_deactivated)
        @include('user._deactivated_sidebar')
        @if (Auth::check() && Auth::user()->isStaff)
            <ul class="my-0 py-0">
                <li class="sidebar-header my-0 h4"><a href="{{ $user->url }}" class="card-link">ADMIN VIEW</a></li>
            </ul>

            @include('user._sidebar')
        @endif
    @else
        @include('user._sidebar')
    @endif
@endsection

@section('content')
    @yield('profile-content')
@endsection

@section('scripts')
    @parent
@endsection
