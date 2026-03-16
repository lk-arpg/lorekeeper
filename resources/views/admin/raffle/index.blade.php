@extends('admin.layout')

@section('admin-title')
    Raffle Index
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Raffle Index' => 'admin/raffles']) !!}

    <h1>Raffle Index</h1>
    <div class="text-right form-group">
        <a class="btn btn-success edit-group" href="#" data-id="">Create Raffle Group</a>
        <a class="btn btn-success edit-raffle" href="#" data-id="">Create Raffle</a>
    </div>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a href="{{ url()->current() }}" class="nav-link {{ Request::get('is_active') ? '' : 'active' }}">Current Raffles</a></li>
        <li class="nav-item"><a href="{{ url()->current() }}?is_active=1" class="nav-link {{ Request::get('is_active') == 1 ? 'active' : '' }}">Open Raffles</a></li>
        <li class="nav-item"><a href="{{ url()->current() }}?is_active=2" class="nav-link {{ Request::get('is_active') == 2 ? 'active' : '' }}">Completed Raffles</a></li>
    </ul>
    @if (Request::get('is_active') == 1)
        <p>
            This is the list of raffles that are visible to users and have not been rolled.
        </p>
    @elseif(Request::get('is_active') == 2)
        <p>
            This is the list of raffles that are complete (have been rolled). These will always be visible to users.
        </p>
    @elseif(!Request::get('is_active'))
        <p>
            This is the list of raffles that have not been rolled, including hidden raffles.
        </p>
    @endif

    @if ($raffles->count())
        @foreach ($raffles as $key => $raffle)
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="d-inline mb-0">
                        {{ $key }}
                        @if ($raffle->first()->group_id > 0)
                            <span class="badge badge-xs {{ $raffle->first()->group->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $raffle->first()->group->is_active ? 'Visible' : 'Hidden' }}
                            </span>

                            @if ($raffle->first()->group->raffles()->whereNotNull('rolled_at')->count() < 1)
                                <div class="float-right">
                                    <a href="#" class="roll-group btn btn-outline-danger btn-sm" data-id="{{ $raffle->first()->group_id }}">
                                        Roll Group
                                    </a>
                                    <a href="#" class="edit-group btn btn-outline-primary btn-sm" data-id="{{ $raffle->first()->group_id }}">
                                        Edit Group
                                    </a>
                                </div>
                            @endif
                        @endif
                    </h3>
                </div>

                <ul class="list-group list-group-flush">
                    @foreach ($raffle as $r)
                        <li class="list-group-item">
                            <i class="fas {{ ($r->group && $r->group->is_active && $r->is_active) || (!$r->group && $r->is_active) ? 'fa-eye' : 'fa-eye-slash' }} mr-2"></i>
                            <a href="{{ url('raffles/view/' . $r->id) }}">
                                {{ $r->name }} {{ $r->is_fto ? ' (FTO / Non-Owner Only)' : '' }}
                            </a>
                            <div class="float-right">
                                <a href="{{ url('admin/raffles/view/' . $r->id) }}" class="btn btn-xs btn-outline-primary p-1" data-toggle="tooltip" title="View Raffle Admin Index">
                                    <i class="fas fa-crown"></i>
                                </a>
                                @if ($r->is_active < 2)
                                    <a href="#" class="roll-raffle btn btn-outline-danger btn-xs p-2" data-id="{{ $r->id }}">
                                        Roll Raffle
                                    </a>
                                    <a href="#" class="edit-raffle btn btn-xs btn-outline-primary p-2" data-id="{{ $r->id }}">
                                        Edit Raffle
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    @else
        <p>No raffles found.</p>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $('.edit-group').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('/admin/raffles/edit/group/') }}/" + $(this).data('id'), 'Edit Raffle Group');
        });
        $('.edit-raffle').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('/admin/raffles/edit/raffle/') }}/" + $(this).data('id'), 'Edit Raffle');
        });
        $('.roll-raffle').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('/admin/raffles/roll/raffle/') }}/" + $(this).data('id'), 'Roll Raffle');
        });
        $('.roll-group').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('/admin/raffles/roll/group/') }}/" + $(this).data('id'), 'Roll Raffle Group');
        });
    </script>
@endsection
