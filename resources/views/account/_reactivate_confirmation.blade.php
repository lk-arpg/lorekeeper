@if (Auth::user()->is_deactivated)
    <p>This will reactivate your account, allowing you to use the site features again. Are you sure you want to do this?</p>
    {!! Form::open(['url' => 'reactivate']) !!}
    <div class="text-end">
        {!! Form::submit('Reactivate', ['class' => 'btn btn-success']) !!}
    </div>
    {!! Form::close() !!}
@else
    <p>Your account is not deactivated.</p>
@endif
