@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Characters
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Characters' => $user->url . '/characters']) !!}

    <h1>
        {!! $user->displayName !!}'s Characters
    </h1>

    @include('user._characters', ['characters' => $characters, 'myo' => false, 'owner' => true, 'userpage_exts' => true])
@endsection
