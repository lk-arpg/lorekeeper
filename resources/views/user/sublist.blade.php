@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s {{ $sublist->name }}
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, $sublist->name => $user->url . '/sublist/' . $sublist->key]) !!}

    <h1>
        {!! $user->displayName !!}'s {{ $sublist->name }}
    </h1>

    @include('user._characters', ['characters' => $characters, 'myo' => false, 'owner' => true, 'userpage_exts' => true])
@endsection
