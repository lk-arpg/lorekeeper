@if ($user->is_banned)
    <p>This will unban the user, removing them from the site blacklist and allowing them to use the site features again. Are you sure you want to do this?</p>
    {!! Form::open(['url' => 'admin/users/' . $user->name . '/unban']) !!}
    <div class="text-end">
        {!! Form::submit('Unban', ['class' => 'btn btn-danger']) !!}
    </div>
    {!! Form::close() !!}
@else
    <p>This user is not banned.</p>
@endif
