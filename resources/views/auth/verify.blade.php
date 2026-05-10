@extends('layouts.app')

@section('title')
    Verify Email
@endsection

@section('content')
    <h1>Verify Email</h1>
    @if (session('resent'))
        <div class="alert alert-success" role="alert">
            {{ __('A fresh verification link has been sent to your email address.') }}
        </div>
    @endif

    {{ __('Before proceeding, please check your email for a verification link.') }}
    {{ __('If you did not receive the email') }},
    <form class="d-inline" method="POST" action="{{ url('email/verification-notification') }}">
        @csrf
        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">
            {{ __('click here to request another') }}
        </button>.
    </form>

    @if (config('lorekeeper.settings.allow_unverified_users_to_modify_emails'))
        <div class="alert alert-warning mt-3" role="alert">
            {{ __('If you need to change your email address, you can do so') }}
            <a href="{{ url('email/update') }}">{{ __('here') }}</a>.
        </div>
    @endif
@endsection
