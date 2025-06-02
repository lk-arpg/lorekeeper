@extends('layouts.app')

@section('title')
    Deactivated Users
@endsection

@section('content')
    {!! breadcrumbs(['Users' => 'users', 'Deactivated' => 'deactivated']) !!}
    <h1>Deactivated Users</h1>

    @if (!$canView)
        {{-- blade-formatter-disable --}}
    @if($key != '0' &&
        ($privacy == 3 ||
        (Auth::check() &&
        ($privacy == 2 ||
        ($privacy == 1 && Auth::user()->isStaff) ||
        ($privacy == 0 && Auth::user()->isAdmin)))))
    {{-- blade-formatter-enable --}}
        <p>This page requires a key to view. Please enter the key below to view the deactivated users.</p>
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
    <div class="row ms-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-12 col-md-4 fw-bold">Username</div>
            <div class="col-4 col-md-2 fw-bold">Primary Alias</div>
            <div class="col-4 col-md-3 fw-bold">Deactivated by</div>
            <div class="col-4 col-md-2 fw-bold">Deactivated at</div>
        </div>
        @foreach ($users as $user)
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                <div class="col-12 col-md-4 ">{!! $user->displayName !!}</div>
                <div class="col-4 col-md-2">{!! $user->displayAlias !!}</div>
                <div class="col-4 col-md-3">{!! $user->deactivated_by == $user->id ? 'Self' : 'Staff' !!}</div>
                <div class="col-4 col-md-2">{!! pretty_date($user->settings->deactivated_at, false) !!}</div>
            </div>
        @endforeach
    </div>
    {!! $users->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $users->total() }} result{{ $users->total() == 1 ? '' : 's' }}
        found.</div>
    @endif
@endsection
