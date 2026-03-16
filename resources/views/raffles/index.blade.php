@extends('layouts.app')

@section('title')
    Raffles
@endsection

@section('content')
    {!! breadcrumbs(['Raffles' => 'raffles']) !!}
    <h1>Raffles</h1>
    <p>Click on the name of a raffle to view the tickets, and in the case of completed raffles, the winners. Raffles in a group with a title will be rolled consecutively starting from the top, and will not draw duplicate winners.</p>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a href="{{ url()->current() }}" class="nav-link {{ Request::get('view') ? '' : 'active' }}">Current Raffles</a></li>
        <li class="nav-item"><a href="{{ url()->current() }}?view=completed" class="nav-link {{ Request::get('view') == 'completed' ? 'active' : '' }}">Completed Raffles</a></li>
    </ul>

    @if (count($raffles))
        @foreach ($raffles as $key => $raffle)
            <div class="card mb-3">
                @if ($key != 'Ungrouped')
                    <div class="card-header">
                        <h3 class="d-inline mb-0">
                            {{ $key }}
                        </h3>
                    </div>
                @endif

                <ul class="list-group list-group-flush">
                    @foreach ($raffle as $r)
                        <li class="list-group-item">
                            <div class="card">
                                <div class="card-header h5 row m-0 {{ !$r->parsed_description && !hasRewards($r) ? 'border-bottom-0' : '' }}" data-toggle="collapse" href="#raffle-{{ $r->id }}">
                                    <div class="col-lg-9 col-12 p-0">
                                        <a href="{{ url('raffles/view/' . $r->id) }}">{{ $r->name }} {{ $r->is_fto ? ' (FTO / Non-Owner Only)' : '' }}</a>
                                        {!! $r->rolled_at ? '<span class="text-muted small">(Rolled ' . pretty_date($r->rolled_at) . ')</span>' : '' !!}
                                        @if ($r->parsed_description || hasRewards($r))
                                            <i class="fas fa-chevron-down float-right mt-1 mr-2"></i>
                                        @endif
                                    </div>
                                    <a class="col-lg-{{ Auth::check() && Auth::user()->isStaff ? '2' : '3' }} col-12 ml-auto btn btn-sm bg-light border" href="{{ url('raffles/view/' . $r->id) }}">
                                        <i class="fas fa-ticket-alt"></i> Tickets
                                    </a>
                                    @if (Auth::check() && Auth::user()->isStaff)
                                        <div class="col-lg-1 col-12">
                                            <x-admin-edit title="Raffle" :object="$r" />
                                        </div>
                                    @endif
                                </div>
                                @if ($r->parsed_description || hasRewards($r))
                                    <div class="card-body collapse show" id="raffle-{{ $r->id }}">
                                        @if ($r->parsed_description)
                                            {!! $r->parsed_description !!}
                                        @endif
                                        @if ($r->parsed_description && hasRewards($r))
                                            <hr>
                                        @endif
                                        @if (getRewards($r, true)->where('data->type', 'winner_reward')->count())
                                            <p>A total of {{ $r->winner_count }} winner(s) will receive the following rewards:</p>
                                            @php
                                                $winnerRewards = getRewards($r, true)->where('data->type', 'winner_reward')->get();

                                                $grouped = $winnerRewards
                                                    ->groupBy(function ($reward) {
                                                        return data_get($reward->data, 'position', 1); // or $reward->data['position'] ?? 1
                                                    })
                                                    ->sortKeys();
                                            @endphp
                                            @foreach ($grouped as $position => $rewards)
                                                <div class="card mb-3">
                                                    <div class="card-header h4">{{ $position ? 'Winner #' . $position : 'All Winners' }}</div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            @foreach ($rewards as $reward)
                                                                <div class="col-md-3 mt-3 text-center">
                                                                    @if ($reward->reward->imageUrl)
                                                                        <div class="mb-2">
                                                                            <img class="border rounded img-fluid" src="{{ $reward->reward->imageUrl }}" alt="{{ $reward->reward->name }}" />
                                                                        </div>
                                                                    @endif
                                                                    <span class="mr-1">{{ $reward->quantity }}x</span> {!! $reward->reward->displayName !!}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                        @if ($r->allow_entry && !$r->rolled_at)
                                            @if (Auth::user())
                                                <a href="{{ url('raffles/join/' . $r->id) }}" class="btn btn-primary float-right @if ($r->tickets()->where('user_id', Auth::user()->id)->count() >= 1) disabled @endif">Join Raffle</a>
                                            @else
                                                <div class="float-right"><i>You must be logged in to join the raffle.</i></div>
                                            @endif
                                        @endif
                                    </div>
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
@endsection
