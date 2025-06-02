@extends('layouts.app')

@section('title')
    Register
@endsection

@section('content')
    @if ($userCount)
        <div class="row">
            <div class="col-md-6 offset-md-4">
                <h1>Register</h1>
            </div>
        </div>

        @if ($altRegistrations)
            <h3 class="text-center">Alternate Registrations</h3>
            @foreach ($altRegistrations as $provider => $site)
                @if (isset($site['login']) && $site['login'])
                    <div class="text-center w-75 m-auto pt-2 pb-2">
                        <a href="{{ url('/login/redirect/' . $provider) }}" class="btn btn-primary text-white w-100"><i class="{{ $site['icon'] }} me-2"></i> Register With {{ ucfirst($provider) }}</a>
                    </div>
                @endif
            @endforeach
        @endif

        <h3 class="mt-5 text-center">Regular Registration</h3>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            @honeypot

            <div class="mb-3 row">
                <label for="name" class="col-md-4 col-form-label text-md-end">Username</label>

                <div class="col-md-6">
                    <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                    @if ($errors->has('name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="mb-3 row">
                <label for="email" class="col-md-4 col-form-label text-md-end">E-mail Address</label>

                <div class="col-md-6">
                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

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
                <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                <div class="col-md-6">
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                </div>
            </div>

            @if (!Settings::get('is_registration_open'))
                <div class="mb-3 row">
                    <label for="name" class="col-md-4 col-form-label text-md-end">Invitation Key {!! add_help('Registration is currently closed. An invitation key is required to create an account.') !!}</label>

                    <div class="col-md-6">
                        <input id="code" type="text" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}" name="code" value="{{ old('code') }}" required autofocus>

                        @if ($errors->has('code'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('code') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="mb-3 row">
                {{ Form::label('dob', 'Date of Birth', ['class' => 'col-md-4 col-form-label text-md-right']) }}
                <div class="col-md-6">
                    {!! Form::date('dob', null, ['class' => 'form-control']) !!}
                </div>
                @if ($errors->has('dob'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('dob') }}</strong>
                    </span>
                @endif
            </div>

            <div class="mb-3 row">
                <div class="col-md-6 offset-md-4">
                    <div class="form-check">
                        <label class="form-check-label">
                            {!! Form::checkbox('agreement', 1, false, ['class' => 'form-check-input']) !!}
                            I have read and agree to the <a href="{{ url('info/terms') }}">Terms of Service</a> and <a href="{{ url('info/privacy') }}">Privacy Policy</a>.
                        </label>
                    </div>
                </div>
            </div>

            @if (config('lorekeeper.extensions.use_recaptcha'))
                {!! RecaptchaV3::field('register') !!}
            @endif

            <div class="mb-3 row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" value="register" class="btn btn-primary">
                        {{ __('Register') }}
                    </button>
                </div>
            </div>
        </form>
    @else
        @include('auth._require_setup')
    @endif
@endsection
