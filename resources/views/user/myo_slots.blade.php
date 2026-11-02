@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s MYO Slots
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'MYO Slots' => $user->url . '/myos']) !!}

    <h1>
        {!! $user->displayName !!}'s MYO Slots
    </h1>

    @include('user._characters', ['characters' => $myos, 'myo' => true, 'owner' => true, 'userpage_exts' => true])
@endsection
