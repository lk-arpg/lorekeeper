@extends('layouts.app')

@section('title')
    Forgot Password
@endsection

@section('content')
    @if (isset($status) || (isset($errors) && count($errors)))
        <div class="alert alert-success mb-4">
            Form submitted successfully. If this email address is registered to an account, you will receive a password reset email.
        </div>
    @endif

    <h1>Forgot Password</h1>

    <p>Please enter the email address associated with your account. An email will be sent to this address to reset your password.</p>

    {!! Form::open(['url' => 'forgot-password']) !!}
    <div class="form-group row">
        {!! Form::label('Email', null, ['class' => 'col-md-3 col-form-label text-md-right']) !!}
        <div class="col-md-7">
            {!! Form::text('email', null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="text-right">
        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@endsection
