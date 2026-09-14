@extends('admin.layout')

@section('admin-title')
    Raffle Tickets for {{ $raffle->name }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Raffle Index' => 'admin/raffles', 'Raffle Tickets for ' . $raffle->name => 'admin/raffles/view/' . $raffle->id]) !!}

    <h1>
        Raffle Tickets: {{ $raffle->name }} {{ $raffle->is_fto ? ' (FTO / Non-Owner Only)' : '' }}
    </h1>

    @if ($raffle->is_active == 0)
        @if ($raffle->end_at)
            @if ($raffle->end_at < Carbon\Carbon::now())
                <div class="alert alert-danger mb-2">This raffle has closed.</div>
            @else
                <p>This raffle is currently hidden. (Number of winners to be drawn: {{ $raffle->winner_count }})</p>
                <div class="alert alert-warning mb-2">This raffle will close {{ $raffle->end_at->format('F j, Y g:i A') }}.</div>
            @endif
        @endif
        @if ($raffle->ticket_cap)
            <p>This raffle has a cap of {{ $raffle->ticket_cap }} tickets per individual.</p>
        @endif
        <div class="text-right form-group">
            <a class="btn btn-success edit-tickets" href="#" data-id="">Add Tickets</a>
        </div>
    @elseif($raffle->is_active == 1)
        @if ($raffle->end_at)
            @if ($raffle->end_at < Carbon\Carbon::now())
                <div class="alert alert-warning mb-2">This raffle has closed.</div>
            @else
                <p>This raffle is currently open. (Number of winners to be drawn: {{ $raffle->winner_count }})</p>
                <div class="alert alert-warning mb-2">This raffle will close {{ $raffle->end_at->format('F j, Y g:i A') }}.</div>
            @endif
        @endif

        @if ($raffle->ticket_cap)
            <p>This raffle has a cap of {{ $raffle->ticket_cap }} tickets per individual.</p>
        @endif

        <div class="text-right form-group">
            <a class="btn btn-success edit-tickets" href="#" data-id="">Add Tickets</a>
        </div>
    @elseif($raffle->is_active == 2)
        <p>This raffle is closed. Rolled: {!! format_date($raffle->rolled_at) !!}</p>
        <div class="card mb-3">
            <div class="card-header h3">Winner(s)</div>
            <div class="logs-table mb-0">
                <div class="logs-table-header">
                    <div class="row no-gutters">
                        <div class="col-1">
                            <div class="logs-table-cell text-center">#</div>
                        </div>
                        <div class="col">
                            <div class="logs-table-cell text-left">User</div>
                        </div>
                    </div>
                </div>
                <div class="logs-table-body">
                    @foreach ($raffle->tickets()->winners()->get() as $winner)
                        <div class="logs-table-row">
                            <div class="row no-gutters align-items-center flex-wrap">
                                <div class="col-1">
                                    <div class="logs-table-cell text-center">{{ $winner->position }}</div>
                                </div>
                                <div class="col">
                                    <div class="logs-table-cell text-left">
                                        {!! $winner->displayHolderName !!}
                                        @if ($winner->reroll)
                                            <span class="text-danger">(Reroll)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="btn btn-primary btn-sm my-1 mr-2 reroll" value="{{ $winner->id }}">Reroll?</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif($raffle->is_active == 1)
        <p>
            This raffle is currently open. (Number of winners to be drawn: {{ $raffle->winner_count }})<br />
            @if ($raffle->ticket_cap)
                This raffle has a cap of {{ $raffle->ticket_cap }} tickets per individual.
            @endif
        </p>
        <div class="text-right form-group">
            <a class="btn btn-success edit-tickets" href="#" data-id="">Add Tickets</a>
        </div>
    @elseif($raffle->is_active == 2)
        <p>This raffle is closed. Rolled: {!! format_date($raffle->rolled_at) !!}</p>
        <div class="card mb-3">
            <div class="card-header h3">Winner(s)</div>
            <div class="mb-4 logs-table mb-0">
                <div class="logs-table-header">
                    <div class="row">
                        <div class="col-1">
                            <div class="logs-table-cell text-center">#</div>
                        </div>
                        <div class="col-11">
                            <div class="logs-table-cell text-left">User</div>
                        </div>
                    </div>
                </div>
                <div class="logs-table-body">
                    @foreach ($raffle->tickets()->winners()->get() as $winner)
                        <div class="logs-table-row">
                            <div class="row flex-wrap">
                                <div class="col-1">
                                    <div class="logs-table-cell text-center">{{ $winner->position }}</div>
                                </div>
                                <div class="col-11">
                                    <div class="logs-table-cell text-left">{!! $winner->displayHolderName !!}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header h5 row m-0 {{ !$raffle->parsed_description && !hasRewards($raffle) ? 'border-bottom-0' : '' }}" data-toggle="collapse" href="#raffle-{{ $raffle->id }}">
            <div class="col-lg-9 col-12 p-0">
                <a href="{{ url('raffles/view/' . $raffle->id) }}">{{ $raffle->name }} {{ $raffle->is_fto ? ' (FTO / Non-Owner Only)' : '' }}</a>
                {!! $raffle->rolled_at ? '<span class="text-muted small">(Rolled ' . pretty_date($raffle->rolled_at) . ')</span>' : '' !!}
                @if ($raffle->parsed_description || hasRewards($raffle))
                    <i class="fas fa-chevron-down float-right mt-1 mr-2"></i>
                @endif
            </div>
            <a class="col-lg-{{ Auth::check() && Auth::user()->isStaff ? '2' : '3' }} col-12 ml-auto btn btn-sm bg-light border" href="{{ url('raffles/view/' . $raffle->id) }}">
                <i class="fas fa-ticket-alt"></i> Tickets
            </a>
            @if (Auth::check() && Auth::user()->isStaff)
                <div class="col-lg-1 col-12">
                    <x-admin-edit title="Raffle" :object="$raffle" />
                </div>
            @endif
        </div>
        @if ($raffle->parsed_description || hasRewards($raffle))
            <div class="card-body collapse show" id="raffle-{{ $raffle->id }}">
                @if ($raffle->parsed_description)
                    {!! $raffle->parsed_description !!}
                @endif
                @if ($raffle->parsed_description && hasRewards($raffle))
                    <hr>
                @endif
                @if (getRewards($raffle, true)->where('data->type', 'winner_reward')->count())
                    <p>A total of {{ $raffle->winner_count }} winner(s) will receive the following rewards:</p>
                    @php
                        $winnerRewards = getRewards($raffle, true)->where('data->type', 'winner_reward')->get();

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
                @if ($raffle->allow_entry && !$raffle->rolled_at)
                    @if (Auth::user())
                        <a href="{{ url('raffles/enter/' . $raffle->id) }}" class="btn btn-primary float-right @if ($raffle->tickets()->where('user_id', Auth::user()->id)->count() >= 1) disabled @endif">Join Raffle</a>
                    @else
                        <div class="float-right"><i>You must be logged in to join the raffle.</i></div>
                    @endif
                @endif
            </div>
        @endif
    </div>

    <h3>Tickets</h3>

    <div class="text-right">{!! $tickets->render() !!}</div>
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row no-gutters">
                <div class="col-2 col-sm-1">
                    <div class="logs-table-cell text-center">#</div>
                </div>
                <div class="col-8 col-md">
                    <div class="logs-table-cell">User</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($tickets as $count => $ticket)
                <div class="logs-table-row">
                    <div class="row no-gutters flex-wrap">
                        <div class="col-2 col-sm-1">
                            <div class="logs-table-cell text-center">{{ $page * 200 + $count + 1 }}</div>
                        </div>
                        <div class="col">
                            <div class="logs-table-cell">{!! $ticket->displayHolderName !!}</div>
                        </div>
                        @if ($raffle->is_active < 2)
                            <div class="col-auto">
                                <div class="logs-table-cell text-right">
                                    {!! Form::open(['url' => 'admin/raffles/view/ticket/delete/' . $ticket->id]) !!}
                                    {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-sm']) !!}
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="text-right">{!! $tickets->render() !!}</div>

    <div class="modal fade" id="raffle-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Add Tickets</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>
                        Select an on-site user or enter an off-site username, as well as the number of tickets to create for them. Any created tickets are in addition to any pre-existing tickets for the user(s).
                    </p>
                    {!! Form::open(['url' => 'admin/raffles/view/ticket/' . $raffle->id]) !!}
                    <div id="ticketList"></div>
                    <div>
                        <a href="#" class="btn btn-primary" id="add-ticket">Add Ticket</a>
                    </div>
                    <div class="text-right">
                        {!! Form::submit('Add', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                    <div class="ticket-row hide mb-2">
                        {!! Form::select('user_id[]', $users, null, ['class' => 'form-control mr-2 user-select', 'placeholder' => 'Select User']) !!}
                        {!! Form::text('alias[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'OR Enter Alias']) !!}
                        {!! Form::number('ticket_count[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Ticket Count', 'min' => 1]) !!}
                        <a href="#" class="remove-ticket btn btn-danger mb-2">×</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('raffles._logs', ['raffle' => $raffle])
@endsection
@section('scripts')
    @parent
    <script>
        $('.reroll').on('click', function(e) {
            e.preventDefault();
            // get value
            var id = $(this).attr('value');
            loadModal("{{ url('/admin/raffles/edit/reroll') }}/" + id, 'Reroll Ticket');
        });
        $('.edit-tickets').on('click', function(e) {
            e.preventDefault();
            $('#raffle-modal').modal('show');
        });

        $(document).ready(function() {
            $('#add-ticket').on('click', function(e) {
                e.preventDefault();
                $('#raffle-modal').modal('show');
            });

            $('#add-ticket').on('click', function(e) {
                e.preventDefault();
                addTicketRow();
            });
            $('.remove-ticket').on('click', function(e) {
                e.preventDefault();
                removeTicketRow($(this));
            })

            function addTicketRow() {
                var $clone = $('.ticket-row').clone();
                $('#ticketList').append($clone);
                $clone.removeClass('hide ticket-row');
                $clone.addClass('d-flex');
                $clone.find('.remove-ticket').on('click', function(e) {
                    e.preventDefault();
                    removeTicketRow($(this));
                })
                $clone.find('.user-select').selectize();
            }

            function removeTicketRow($trigger) {
                $trigger.parent().remove();
            }
        });
    </script>
@endsection
