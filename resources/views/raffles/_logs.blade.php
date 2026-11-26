@if ($raffle->logs->count() && $raffle->logs()->where('type', 'Reward')->get()->count() && Auth::check() && Auth::user()->isStaff)
    <div class="card mb-3 mt-3">
        <div class="card-header h3">Users Rewarded For Entry</div>
        <div class="card-body">
            <div class="logs-table">
                <div class="logs-table-header">
                    <div class="row no-gutters">
                        <div class="col">
                            <div class="logs-table-cell">User</div>
                        </div>
                        <div class="col-3">
                            <div class="logs-table-cell">Date</div>
                        </div>
                    </div>
                </div>
                <div class="logs-table-body">
                    @foreach ($raffle->logs()->where('type', 'Reward')->orderBy('created_at', 'DESC')->get() as $log)
                        <div class="logs-table-row">
                            <div class="row no-gutters flex-wrap">
                                <div class="col">
                                    <div class="logs-table-cell">{!! $log->user->displayName !!}</div>
                                </div>
                                <div class="col-3">
                                    <div class="logs-table-cell">{!! pretty_date($log->created_at) !!}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

@if ($raffle->logs->count() && $raffle->logs()->where('type', 'Reroll')->get()->count())
    <div class="card mb-3 mt-3">
        <div class="card-header h3">Raffle Changelog</div>
        <div class="logs-table">
            <div class="logs-table-header">
                <div class="row no-gutters">
                    <div class="col-6 col-md">
                        <div class="logs-table-cell">Winner Rerolled</div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="logs-table-cell">Staff</div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="logs-table-cell">Reason</div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="logs-table-cell">Date</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($raffle->logs()->where('type', 'Reroll')->orderBy('created_at', 'DESC')->get() as $log)
                    <div class="logs-table-row">
                        <div class="row no-gutters flex-wrap">
                            <div class="col-6 col-md">
                                <div class="logs-table-cell">{!! $log->ticket->displayHolderName !!}</div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="logs-table-cell">{!! $log->user->displayName !!}</div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="logs-table-cell">{!! $log->reason !!}</div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="logs-table-cell">{!! pretty_date($log->created_at) !!}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
