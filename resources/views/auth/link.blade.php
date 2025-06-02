@extends('layouts.app')

@section('title')
    Link Account
@endsection

@section('content')
    <h1>Link Account</h1>
    <p>Your account does not have any external social media accounts linked to it. For the purposes of verifying your identity, you must link at least one of the following accounts to your {{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}
        account. This will give you access to more personalised features of this site.</p>

    <div class="alert alert-warning">You can add other accounts after this process has been completed. Please make sure you are logged into the correct account before continuing.</div>

    <div class="mx-auto" style="width: 250px;">
        @foreach (config('lorekeeper.sites') as $provider => $site)
            @if (isset($site['auth']) && $site['auth'] && isset($site['primary_alias']) && $site['primary_alias'])
                <div class="d-flex mb-3">
                    <div class="d-flex justify-content-end align-items-center"><i class="{{ $site['icon'] }} fa-fw me-3"></i></div>
                    <div class=""><a href="{{ url('auth/redirect/' . $provider) }}" class="btn btn-outline-primary">Link <strong>{{ $site['full_name'] }}</strong> Account</a></div>
                </div>
            @endif
        @endforeach
    </div>
@endsection
