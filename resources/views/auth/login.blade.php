@extends('layouts.app')

@section('title')
    Login
@endsection

@section('content')
    @if ($userCount)
        @if (session('status'))
            <div class="alert alert-success mb-4">
                {{ session('status') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-6 offset-md-4">
                <h1>Log In</h1>
            </div>
        </div>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            @honeypot

            <div class="mb-3 row">
                <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('E-Mail Address') }}</label>

                <div class="col-md-6">
                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="mb-3 row">
                <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-md-6 offset-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3 row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Login') }}
                    </button>

                    @if (Route::has('password.request'))
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                </div>
            </div>
        </form>

        @if ($altLogins)
            <h3 class="text-center mt-5 pt-2">Alternate Logins</h3>
            @foreach ($altLogins as $provider => $site)
                @if (isset($site['login']) && $site['login'])
                    <div class="text-center pt-3 w-75 m-auto">
                        <a href="{{ url('/login/redirect/' . $provider) }}" class="btn btn-primary text-white w-100"><i class="{{ $site['icon'] }} me-2"></i> Login With {{ ucfirst($provider) }}</a>
                    </div>
                @endif
            @endforeach
        @endif
    @else
        @include('auth._require_setup')
    @endif
@endsection
