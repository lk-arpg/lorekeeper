@extends('account.layout')

@section('account-title')
    Settings
@endsection

@section('account-content')
    {!! breadcrumbs(['My Account' => Auth::user()->url, 'Deactivate Account' => 'account/deactivate']) !!}

    <h1>Deactivate Account</h1>

    <p>
        If you wish to deactivate your account for whatever reason, you may do so here. This does not delete your account in totality, but it hides your information
        from the public. Due to the way our website is set up, this is the closest we can get to account deletion. You will be able to reactivate your account at any time.
    </p>
    <p>
        This will automatically cancel all pending design updates, submissions, and trades associated with your account.
    </p>

    <div class="card p-3 mb-2">
        <h3>Deactivate your account</h3>
        {!! Form::open(['url' => 'account/deactivate', 'id' => 'deactivateForm']) !!}
        <div class="mb-3">
            {!! Form::label('Reason (Optional; no HTML)') !!}
            {!! Form::textarea('deactivate_reason', Auth::user()->settings->deactivate_reason, ['class' => 'form-control']) !!}
        </div>
        <div class="text-end">
            {!! Form::submit(Auth::user()->is_deactivated ? 'Edit' : 'Deactivate', ['class' => 'btn btn' . (Auth::user()->is_deactivated ? '' : '-outline') . '-danger deactivate-button']) !!}
        </div>
        {!! Form::close() !!}
    </div>
@endsection
