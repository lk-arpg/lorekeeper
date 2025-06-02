@extends('admin.layout')

@section('admin-title')
    Raffle Tickets for {{ $raffle->name }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Raffle Index' => 'admin/raffles', 'Raffle Tickets for ' . $raffle->name => 'admin/raffles/view/' . $raffle->id]) !!}

    <h1>
        Raffle Tickets: {{ $raffle->name }}</h1>

    @if ($raffle->is_active == 0)
        <p>
            This raffle is currently hidden. (Number of winners to be drawn: {{ $raffle->winner_count }})<br />
            @if ($raffle->ticket_cap)
                This raffle has a cap of {{ $raffle->ticket_cap }} tickets per individual.
            @endif
        </p>
        <div class="text-end mb-3">
            <a class="btn btn-success edit-tickets" href="#" data-id="">Add Tickets</a>
        </div>
    @elseif($raffle->is_active == 1)
        <p>
            This raffle is currently open. (Number of winners to be drawn: {{ $raffle->winner_count }})<br />
            @if ($raffle->ticket_cap)
                This raffle has a cap of {{ $raffle->ticket_cap }} tickets per individual.
            @endif
        </p>
        <div class="text-end mb-3">
            <a class="btn btn-success edit-tickets" href="#" data-id="">Add Tickets</a>
        </div>
    @elseif($raffle->is_active == 2)
        <p>This raffle is closed. Rolled: {!! format_date($raffle->rolled_at) !!}</p>
        <div class="card mb-3">
            <div class="card-header h3">Winner(s)</div>
            <div class="table-responsive">
                <div class="mb-4 logs-table mb-0">
                    <div class="logs-table-header">
                        <div class="row">
                            <div class="col-1">
                                <div class="logs-table-cell text-center">#</div>
                            </div>
                            <div class="col-11">
                                <div class="logs-table-cell text-start">User</div>
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
                                        <div class="logs-table-cell text-start">{!! $winner->displayHolderName !!}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <h3>Tickets</h3>

    <div class="text-end">{!! $tickets->render() !!}</div>
    <div class="table-responsive">
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-1">
                        <div class="logs-table-cell text-center">#</div>
                    </div>
                    <div class="col-8 col-md-3">
                        <div class="logs-table-cell text-start">User</div>
                    </div>
                    @if ($raffle->is_active < 2)
                        <div class="col-3">
                            <div class="logs-table-cell"></div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($tickets as $count => $ticket)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-1">
                                <div class="logs-table-cell text-center">{{ $page * 200 + $count + 1 }}</div>
                            </div>
                            <div class="col-8 col-md-3">
                                <div class="logs-table-cell text-start">{!! $ticket->displayHolderName !!}</div>
                            </div>
                            @if ($raffle->is_active < 2)
                                <div class="col-3">
                                    <div class="logs-table-cell text-end">{!! Form::open(['url' => 'admin/raffles/view/ticket/delete/' . $ticket->id]) !!}{!! Form::submit('Delete', ['class' => 'btn btn-danger btn-sm']) !!}{!! Form::close() !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="text-end">{!! $tickets->render() !!}</div>

    <div class="modal fade" id="raffle-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Add Tickets</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Select an on-site user or enter an off-site username, as well as the number of tickets to create for them. Any created tickets are in addition to any pre-existing tickets for the user(s).</p>
                    {!! Form::open(['url' => 'admin/raffles/view/ticket/' . $raffle->id]) !!}
                    <div id="ticketList">
                    </div>
                    <div><a href="#" class="btn btn-primary" id="add-ticket">Add Ticket</a></div>
                    <div class="text-end">
                        {!! Form::submit('Add', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                    <div class="ticket-row hide mb-2">
                        {!! Form::select('user_id[]', $users, null, ['class' => 'form-control me-2 user-select', 'placeholder' => 'Select User']) !!}
                        {!! Form::text('alias[]', null, ['class' => 'form-control me-2', 'placeholder' => 'OR Enter Alias']) !!}
                        {!! Form::number('ticket_count[]', null, ['class' => 'form-control me-2', 'placeholder' => 'Ticket Count']) !!}
                        <a href="#" class="remove-ticket btn btn-danger mb-2">�</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    <script>
        $('.edit-tickets').on('click', function(e) {
            e.preventDefault();
            $('#raffle-modal').modal('show');
        });

        $(document).ready(function() {
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
