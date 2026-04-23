@extends('admin.layout')

@section('admin-title')
    User Index
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'User Index' => 'admin/users', $user->name => 'admin/users/' . $user->name . '/edit']) !!}

    <h1>User: {!! $user->displayName !!}</h1>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" href="{{ $user->adminUrl }}">Account</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('admin/users/' . $user->name . '/updates') }}">Account Updates</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('admin/users/' . $user->name . '/ban') }}">Ban</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('admin/users/' . $user->name . '/deactivate') }}">Deactivate</a>
        </li>
    </ul>

    <div class="card p-3 mb-2">
        <h3>Basic Info</h3>
        {!! Form::open(['url' => 'admin/users/' . $user->name . '/basic']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Username</label>
            <div class="col-md-10">
                {!! Form::text('name', $user->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Rank
                @if ($user->isAdmin)
                    {!! add_help('The rank of the admin user cannot be edited.') !!}
                @elseif(!Auth::user()->canEditRank($user->rank))
                    {!! add_help('Your rank is not high enough to edit this user.') !!}
                @endif
            </label>
            <div class="col-md-10">
                @if (!$user->isAdmin && Auth::user()->canEditRank($user->rank))
                    {!! Form::select('rank_id', $ranks, $user->rank_id, ['class' => 'form-control']) !!}
                @else
                    {!! Form::text('rank_id', $ranks[$user->rank_id], ['class' => 'form-control', 'disabled']) !!}
                @endif
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Account</h3>
        {!! Form::open(['url' => 'admin/users/' . $user->name . '/account']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Email Address</label>
            <div class="col-md-10">
                {!! Form::text('email', $user->email, ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Join Date</label>
            <div class="col-md-10">
                {!! Form::text('created_at', format_date($user->created_at, false), ['class' => 'form-control', 'disabled']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Is an FTO {!! add_help(
                'FTO (First Time Owner) means that they have no record of possessing a character from this world. This status is automatically updated when they earn their first character, but can be toggled manually in case off-record transfers have happened before.',
            ) !!}</label>
            <div class="col-md-10">
                <div class="form-check form-control-plaintext">
                    {!! Form::checkbox('is_fto', 1, $user->settings->is_fto, ['class' => 'form-check-input', 'id' => 'checkFTO']) !!}
                </div>
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Birthdate</h3>
        @if ($user->birthday)
            <p>This user's birthday is set to {{ format_date($user->birthday, false) }}.</p>
            @if (!$user->checkBirthday)
                <p class="text-danger">This user is currently set to an underage DOB.</p>
            @endif
        @else
            <p class="text-danger">This user has not set their DOB.</p>
        @endif
        {!! Form::open(['url' => 'admin/users/' . $user->name . '/birthday']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Date of Birth</label>
            <div class="col-md-10 row">
                {!! Form::date('dob', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Aliases</h3>
        <p>As users are supposed to verify that they own their account(s) themselves, aliases cannot be edited directly. If a user wants to change their alias, clear it here and ask them to go through the verification process again while logged into
            their new account. If the alias is the user's primary alias, their remaining aliases will be checked to see if they have a valid primary alias. If they do, it will become their new primary alias.</p>
        @if ($user->aliases->count())
            @foreach ($user->aliases as $alias)
                <div class="form-group row">
                    <div class="col-2">
                        <label>Alias{{ $alias->is_primary_alias ? ' (Primary)' : '' }}</label>
                    </div>
                    <div class="col-10">
                        <div class="d-flex">
                            {!! Form::text('alias', $alias->alias . '@' . $alias->siteDisplayName . (!$alias->is_visible ? ' (Hidden)' : ''), ['class' => 'form-control', 'disabled']) !!}
                            {!! Form::open(['url' => 'admin/users/' . $user->name . '/alias/' . $alias->id]) !!}
                            <div class="text-right ml-2">{!! Form::submit('Clear Alias', ['class' => 'btn btn-danger']) !!}</div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            @endforeach
        @else
            <p>No aliases found.</p>
        @endif
    </div>

    <div class="card p-3 mb-2">
        <h3>API Tokens</h3>
        <p>As an admin, you are able to revoke a user's API tokens at any time.</p>
        <p>This user currently has {{ $user->tokens()->count() }} active API token(s).</p>
        {!! Form::open(['url' => 'admin/users/' . $user->name . '/revoke']) !!}
        {!! Form::submit('Revoke Tokens', ['class' => 'btn btn-danger revoke-button']) !!}
        {!! Form::close() !!}
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.revoke-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/users/' . $user->name . '/revoke-confirm') }}", 'Revoke API Tokens');
            });
        });
    </script>
@endsection
