<div class="transfer-row mb-2">
    @if ($transfer->character)
        <div class="transfer-thumbnail"><a href="{{ $transfer->character->url }}"><img src="{{ $transfer->character->image->thumbnailUrl }}" class="img-thumbnail" alt="Thumbnail for {{ $transfer->character->fullName }}" /></a></div>
    @endif
    <div class="transfer-info card ms-2">
        <div class="card-body">
            <div class="transfer-info-content">
                @if ($transfer->character)
                    <h3 class="mb-0 transfer-info-header"><a href="{{ $transfer->character->url }}">{{ $transfer->character->fullName }}</a></h3>
                @else
                    <h3 class="mb-0 transfer-info-header">Character Deleted</h3>
                @endif
                <div class="transfer-info-body mb-3">
                    @if (Auth::user()->id == $transfer->recipient_id)
                        <p>Transfer sent by {!! $transfer->sender->displayName !!}, {!! format_date($transfer->created_at) !!}</p>
                    @else
                        <p>Transfer sent to {!! $transfer->recipient->displayName !!}, {!! format_date($transfer->created_at) !!}</p>
                    @endif

                    @if ($transfer->isActive && $transfersQueue)
                        @if ($transfer->is_approved)
                            <h5 class="mb-0"><i class="text-success far fa-circle fa-fw me-2"></i> Transfer approved {!! add_help('This transfer has been approved by a mod and will be processed once accepted.') !!}</h5>
                        @else
                            <h5 class="mb-0"><i class="text-danger fas fa-times fa-fw me-2"></i> Transfer awaiting approval {!! add_help('This transfer has not been approved by a mod yet. Once approved and accepted by the recipient, it will be processed.') !!}</h5>
                        @endif
                    @elseif(!$transfer->isActive)
                        @if ($transfer->reason)
                            <div class="alert alert-danger mb-0">{{ $transfer->reason }}</div>
                        @endif
                    @endif
                </div>
                <div class="transfer-info-footer">
                    @if (Auth::user()->id == $transfer->recipient_id)
                        @if ($transfer->isActive)
                            @if ($transfer->status == 'Pending')
                                {!! Form::open(['url' => 'characters/transfer/act/' . $transfer->id, 'class' => 'text-end']) !!}
                                {!! Form::submit('Accept', ['class' => 'btn btn-success', 'name' => 'action']) !!}
                                {!! Form::submit('Reject', ['class' => 'btn btn-danger', 'name' => 'action']) !!}
                                {!! Form::close() !!}
                            @else
                                Currently awaiting mod approval
                            @endif
                        @else
                            <h2 class="text-end mb-0">
                                @if ($transfer->status == 'Accepted')
                                    <span class="badge text-bg-success">Accepted</span>
                                @elseif($transfer->status == 'Rejected')
                                    <span class="badge text-bg-danger">Rejected</span>
                                @elseif($transfer->status == 'Canceled')
                                    <span class="badge text-bg-secondary">Canceled</span>
                                @endif
                            </h2>
                        @endif
                    @else
                        @if ($transfer->isActive)
                            @if ($transfer->status == 'Pending')
                                {!! Form::open(['url' => 'characters/transfer/act/' . $transfer->id, 'class' => 'text-end']) !!}
                                {!! Form::submit('Cancel', ['class' => 'btn btn-danger', 'name' => 'action']) !!}
                                {!! Form::close() !!}
                            @endif
                        @else
                            <h2 class="text-end mb-0">
                                @if ($transfer->status == 'Accepted')
                                    <span class="badge text-bg-success">Accepted</span>
                                @elseif($transfer->status == 'Rejected')
                                    <span class="badge text-bg-danger">Rejected</span>
                                @elseif($transfer->status == 'Canceled')
                                    <span class="badge text-bg-secondary">Canceled</span>
                                @endif
                            </h2>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
