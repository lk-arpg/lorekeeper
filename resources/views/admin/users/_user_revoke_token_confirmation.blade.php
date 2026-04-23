<p>Are you sure you want to revoke all of {!! $user->displayName !!}'s API tokens?</p>
{!! Form::open(['url' => 'admin/users/' . $user->name . '/revoke']) !!}
{!! Form::submit('Revoke Tokens', ['class' => 'btn btn-danger']) !!}
{!! Form::close() !!}
