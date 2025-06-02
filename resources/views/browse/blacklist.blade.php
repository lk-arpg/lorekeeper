@extends('layouts.app')

@section('title')
    Blacklist
@endsection

@section('content')
    {!! breadcrumbs(['Users' => 'users', 'Blacklist' => 'blacklist']) !!}
    <h1>User Blacklist</h1>

    @if (!$canView)
        {{-- blade-formatter-disable --}}
    @if($key != '0' &&
        ($privacy == 3 ||
        (Auth::check() &&
        ($privacy == 2 ||
        ($privacy == 1 && Auth::user()->isStaff) ||
        ($privacy == 0 && Auth::user()->isAdmin)))))
        {{-- blade-formatter-enable --}}
        <p>This page requires a key to view. Please enter the key below to view the blacklist.</p>
        @if (Request::get('key'))
            <p class="text-danger">Incorrect key entered.</p>
        @endif
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline']) !!}
        <div class="mb-3 me-3 mb-3">
            {!! Form::text('key', null, ['class' => 'form-control']) !!}
        </div>
        <div class="mb-3 mb-3">
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @else
        <p>You cannot view this page.</p>
    @endif
@else
    {!! $users->render() !!}
    <table class="users-table table table-sm table-responsive-xs">
        <thead>
            <tr>
                <th>Username</th>
                <th>Alias</th>
                <th>Banned</th>
                <th>Ban Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{!! $user->displayName !!}</td>
                    <td>{!! $user->displayAlias !!}</td>
                    <td>{!! format_date($user->settings->banned_at) !!}</td>
                    <td>{!! nl2br(htmlentities($user->settings->ban_reason)) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $users->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $users->total() }} result{{ $users->total() == 1 ? '' : 's' }}
        found.</div>
    @endif
@endsection
