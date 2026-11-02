@extends('layouts.app')

@section('title')
    Add Email Address
@endsection

@section('content')
    <h1>Add Email Address</h1>
    <p>
        Your account does not have any email addresses linked to it. For the purposes of ensuring account security, you must link at your email address to your {{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}
        account.
        This will ensure that you can recover your account if you forget your password or if off-site providers are disabled.
    </p>


    {!! Form::open(['url' => 'email', 'method' => 'POST']) !!}

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
            {!! Form::submit('Add Email Address', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>

    {!! Form::close() !!}
@endsection
