@extends('layouts.app')

@section('title')
    Update Email Address
@endsection

@section('content')
    <h1>Update Email Address</h1>
    <p class="mb-1">
        The currently linked email to your account is:
    </p>
    <div class="alert alert-secondary mb-2" role="alert">
        {{ Auth::user()->email }}
    </div>
    <p>
        If this email address is incorrect, please enter the correct email address below to update it.
    </p>


    {!! Form::open(['url' => 'email/update', 'method' => 'POST']) !!}

    <div class="form-group row">
        {!! Form::label('email', 'Email Address', ['class' => 'col-md-4 col-form-label text-md-right']) !!}
        <div class="col-md-6">
            {!! Form::email('email', old('email'), ['class' => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''), 'required']) !!}
            @if ($errors->has('email'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-6 offset-md-4">
            {!! Form::submit('Update Email Address', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>

    {!! Form::close() !!}
@endsection
