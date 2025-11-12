@extends('layouts.app')

@section('title')
    Register
@endsection

@section('content')
    @if ($userCount)
        <div class="row">
            <div class="col-md-6 offset-md-4">
                <h1>Register with {{ $provider }}</h1>
            </div>
        </div>
        <form method="POST" action="{{ url('register/' . $provider) }}">
            @csrf
            {!! Form::hidden('token', $token ?? old('token')) !!}

            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">Username</label>

                <div class="col-md-6">
                    <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') ?? $user }}" required autofocus>

                    @if ($errors->has('name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            @if (!Settings::get('is_registration_open'))
                <div class="form-group row">
                    <label for="name" class="col-md-4 col-form-label text-md-right">Invitation Key {!! add_help('Registration is currently closed. An invitation key is required to create an account.') !!}</label>

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

            <div class="form-group row">
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

            <div class="form-group row">
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

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Register') }}
                    </button>
                </div>
            </div>
        </form>
    @else
        @include('auth._require_setup')
    @endif
@endsection
