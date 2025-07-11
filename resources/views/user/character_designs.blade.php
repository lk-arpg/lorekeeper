@extends('user.layout')

@section('profile-title')
    @if ($isDesign)
        {{ $user->name }}'s Character Designs
    @else
        {{ $user->name }}'s Character Art
    @endif
@endsection

@section('profile-content')
    @if ($isDesign)
        {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Character Designs' => $user->url . '/designs']) !!}
    @else
        {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Character Art' => $user->url . '/art']) !!}
    @endif

    <h1>
        @if ($isDesign)
            {!! $user->displayName !!}'s Character Designs
        @else
            {!! $user->displayName !!}'s Character Art
        @endif
    </h1>

    @include('user._characters', ['characters' => $characters, 'myo' => false, 'owner' => false, 'userpage_exts' => false])
@endsection
