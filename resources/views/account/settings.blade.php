@extends('account.layout')

@section('account-title')
    Settings
@endsection

@section('account-content')
    {!! breadcrumbs(['My Account' => Auth::user()->url, 'Settings' => 'account/settings']) !!}

    <h1>Settings</h1>


    <div class="card p-3 mb-2">
        <h3>Avatar</h3>
        @if (Auth::user()->isStaff)
            <div class="alert alert-info">For admins - note that .GIF avatars leave a tmp file in the directory (e.g php2471.tmp). There is an automatic schedule to delete these files.
            </div>
        @endif
        {!! Form::open(['url' => 'account/avatar', 'files' => true]) !!}
        <div class="card mb-3 hide" id="avatarCrop">
            <div class="card-body">
                <img src="#" id="cropper" class="hide" alt="" />
                {!! Form::hidden('x0', null, ['id' => 'cropX0']) !!}
                {!! Form::hidden('x1', null, ['id' => 'cropX1']) !!}
                {!! Form::hidden('y0', null, ['id' => 'cropY0']) !!}
                {!! Form::hidden('y1', null, ['id' => 'cropY1']) !!}
            </div>
            <div class="alert alert-info mx-3">
                <b>Note:</b> Cropping does not work on gifs.
            </div>
        </div>
        <div class="custom-file mb-1">
            {!! Form::label('avatar', 'Update Profile Image', ['class' => 'custom-file-label']) !!}
            {!! Form::file('avatar', ['class' => 'custom-file-input', 'id' => 'avatar']) !!}
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (config('lorekeeper.settings.allow_username_changes'))
        <div class="card p-3 mb-2">
            <h3>Change Username</h3>
            @if (config('lorekeeper.settings.username_change_cooldown'))
                <div class="alert alert-info">
                    You can change your username once every {{ config('lorekeeper.settings.username_change_cooldown') }} days.
                </div>
                @if (Auth::user()->logs()->where('type', 'Username Change')->orderBy('created_at', 'desc')->first())
                    <div class="alert alert-warning">
                        You last changed your username on {{ Auth::user()->logs()->where('type', 'Username Change')->orderBy('created_at', 'desc')->first()->created_at->format('F jS, Y') }}.
                        <br />
                        <b>
                            You will be able to change your username again on
                            {{ Auth::user()->logs()->where('type', 'Username Change')->orderBy('created_at', 'desc')->first()->created_at->addDays(config('lorekeeper.settings.username_change_cooldown'))->format('F jS, Y') }}.
                        </b>
                    </div>
                @endif
            @endif
            {!! Form::open(['url' => 'account/username']) !!}
            <div class="form-group row">
                <label class="col-md-2 col-form-label">Username</label>
                <div class="col-md-10">
                    {!! Form::text('username', Auth::user()->name, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="text-right">
                {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    @endif

    <div class="card p-3 mb-2">
        <h3>Profile</h3>
        {!! Form::open(['url' => 'account/profile']) !!}
        <div class="form-group">
            {!! Form::label('text', 'Profile Text') !!}
            {!! Form::textarea('text', Auth::user()->profile->text, ['class' => 'form-control wysiwyg']) !!}
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Birthday Publicity</h3>
        {!! Form::open(['url' => 'account/dob']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Setting</label>
            <div class="col-md-10">
                {!! Form::select(
                    'birthday_setting',
                    ['0' => '0: No one can see your birthday.', '1' => '1: Members can see your day and month.', '2' => '2: Anyone can see your day and month.', '3' => '3: Full date public.'],
                    Auth::user()->settings->birthday_setting,
                    ['class' => 'form-control'],
                ) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Allow Profile Comments</h3>
        {!! Form::open(['url' => 'account/comments']) !!}
        <p>If turned off, all comments on your profile will be hidden.</p>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Setting</label>
            <div class="col-md-10">
                {!! Form::select('allow_profile_comments', ['0' => '0: No one can comment on your profile.', '1' => '1: Users can comment on your profile.'], Auth::user()->settings->allow_profile_comments, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Character Warning Visibility</h3>
        <p>This setting will change how characters with content warnings are displayed to you.</p>
        {!! Form::open(['url' => 'account/warning']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Setting</label>
            <div class="col-md-10">
                {!! Form::select(
                    'content_warning_visibility',
                    ['0' => '0: Character has pop-up warning and censored icons.', '1' => '1: Character has pop-up warnings only.', '2' => '2: No warnings will appear on characters.'],
                    Auth::user()->settings->content_warning_visibility,
                    ['class' => 'form-control'],
                ) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Email Address</h3>
        <p>Changing your email address will require you to re-verify your email address.</p>
        {!! Form::open(['url' => 'account/email']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Email Address</label>
            <div class="col-md-10">
                {!! Form::text('email', Auth::user()->email, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Change Password</h3>
        {!! Form::open(['url' => 'account/password']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Old Password</label>
            <div class="col-md-10">
                {!! Form::password('old_password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">New Password</label>
            <div class="col-md-10">
                {!! Form::password('new_password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Confirm New Password</label>
            <div class="col-md-10">
                {!! Form::password('new_password_confirmation', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Two-Factor Authentication</h3>

        <p>Two-factor authentication acts as a second layer of protection for your account. It uses an app on your phone-- such as Google Authenticator-- and information provided by the site to generate a random code that changes frequently.</p>

        <div class="alert alert-info">
            Please note that two-factor authentication is only used when logging in directly to the site (with an email address and password), and not when logging in via an off-site account. If you log in using an off-site account, consider enabling
            two-factor authentication on that site instead!
        </div>

        @if (!isset(Auth::user()->two_factor_secret))
            <p>In order to enable two-factor authentication, you will need to scan a QR code with an authenticator app on your phone. Two-factor authentication will not be enabled until you do so and confirm by entering one of the codes provided by your
                authentication app.</p>
            {!! Form::open(['url' => 'account/two-factor/enable']) !!}
            <div class="text-right">
                {!! Form::submit('Enable', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @elseif(isset(Auth::user()->two_factor_secret))
            <p>Two-factor authentication is currently enabled.</p>

            <h4>Disable Two-Factor Authentication</h4>
            <p>To disable two-factor authentication, you must enter a code from your authenticator app.</p>
            {!! Form::open(['url' => 'account/two-factor/disable']) !!}
            <div class="form-group row">
                <label class="col-md-2 col-form-label">Code</label>
                <div class="col-md-10">
                    {!! Form::text('code', null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="text-right">
                {!! Form::submit('Disable', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @endif
    </div>

    @if (Settings::get('allow_users_to_generate_tokens'))
        <div class="card p-3 mb-2">
            <h3>API Token</h3>

            <p>Here you may generate personal access (API) tokens for your account. It will have the same site permissions as you.</p>
            <div class="alert alert-warning">Be warned: giving away this token is like giving away your password!</div>

            @if (Auth::user()->tokens->count())
                <div class="card p-3 mb-2">
                    <h3>Regenerate Token</h3>
                    <p>You already have an API token generated. You may regenerate this token with the button below. It will create a new token, <b>not</b> show you your current one.</p>
                    {!! Form::open(['url' => 'account/api/token']) !!}
                    {!! Form::submit('Regenerate Token', ['class' => 'btn btn-warning']) !!}
                    {!! Form::close() !!}
                </div>
                <div class="card p-3 mb-2">
                    <h3>Revoke Token</h3>
                    <p>Click the below button to <b>revoke</b> (delete) your current API token. <b>This can not be undone!</b></p>
                    {!! Form::open(['url' => 'account/api/revoke']) !!}
                    {!! Form::submit('Revoke Token', ['class' => 'btn btn-danger']) !!}
                    {!! Form::close() !!}

                </div>
            @else
                <div class="card p-3 mb-2">
                    <h3>Generate Token</h3>
                    <p>Clicking the below button will generate a token for your account. This token will only display ONCE. Be sure to copy it down!</p>
                    {!! Form::open(['url' => 'account/api/token']) !!}
                    {!! Form::submit('Generate Token', ['class' => 'btn btn-primary']) !!}
                    {!! Form::close() !!}
                </div>
            @endif
        </div>
    @endif

@endsection
@section('scripts')
    @include('js._tinymce_wysiwyg')
    <script>
        var $avatarCrop = $('#avatarCrop');
        var $cropper = $('#cropper');
        var c = null;
        var $x0 = $('#cropX0');
        var $y0 = $('#cropY0');
        var $x1 = $('#cropX1');
        var $y1 = $('#cropY1');
        var zoom = 0;

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $cropper.attr('src', e.target.result);
                    c = new Croppie($cropper[0], {
                        viewport: {
                            width: 200,
                            height: 200,
                        },
                        boundary: {
                            width: 250,
                            height: 250
                        },
                        update: function() {
                            updateCropValues();
                        }
                    });
                    updateCropValues();
                    $avatarCrop.removeClass('hide');
                    $cropper.removeClass('hide');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#avatar").change(function() {
            readURL(this);
        });

        function updateCropValues() {
            var values = c.get();
            $x0.val(values.points[0]);
            $y0.val(values.points[1]);
            $x1.val(values.points[2]);
            $y1.val(values.points[3]);
        }
    </script>
@endsection
