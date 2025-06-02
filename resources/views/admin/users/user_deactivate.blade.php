@extends('admin.layout')

@section('admin-title')
    Deactivate User: {{ $user->name }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'User Index' => 'admin/users', $user->name => 'admin/users/' . $user->name . '/edit', 'Deactivate User' => 'admin/users/' . $user->name . '/deactivate']) !!}

    <h1>User: {!! $user->displayName !!}</h1>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link" href="{{ $user->adminUrl }}">Account</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('admin/users/' . $user->name . '/updates') }}">Account Updates</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('admin/users/' . $user->name . '/ban') }}">Ban</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ url('admin/users/' . $user->name . '/deactivate') }}">Deactivate</a>
        </li>
    </ul>

    <h3>{{ $user->is_deactivated ? 'Edit Deactivation' : 'Deactivate' }}</h3>
    <p>Deactivating the user will remove their rank, cancel all of their queued submissions and transfers, and prevent them from using any other site features. The deactivate reason will be displayed on the blacklist.</p>

    {!! Form::open(['url' => 'admin/users/' . $user->name . '/deactivate', 'id' => 'deactivateForm']) !!}
    <div class="mb-3">
        {!! Form::label('Reason (Optional; no HTML)') !!}
        {!! Form::textarea('deactivate_reason', $user->settings->deactivate_reason, ['class' => 'form-control']) !!}
    </div>
    <div class="text-end">
        {!! Form::submit($user->is_deactivated ? 'Edit' : 'Deactivate', ['class' => 'btn btn' . ($user->is_deactivated ? '' : '-outline') . '-danger deactivate-button']) !!}
    </div>
    {!! Form::close() !!}

    @if ($user->is_deactivated)
        <h3>Reactivate</h3>
        <p>Reactivating the user will grant them access to site features again. However, if they had a rank before being deactivatened, it will not be restored.</p>
        <div class="text-end">
            <a href="#" class="btn btn-outline-danger reactivate-button">Reactivate</a>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            @if (!$user->is_deactivated)
                $('.deactivate-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/users/' . $user->name . '/deactivate-confirm') }}", 'Deactivate User');
                });
            @else
                $('.reactivate-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/users/' . $user->name . '/reactivate-confirm') }}", 'Reactivate User');
                });
            @endif
        });
    </script>
@endsection
