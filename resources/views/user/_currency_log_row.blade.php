<div class="row flex-wrap">
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            <i
                class="{{ $log->recipient_id == $owner->id && $log->recipient_type == $owner->logType ? 'in' : 'out' }}flow bg-{{ $log->recipient_id == $owner->id && $log->recipient_type == $owner->logType ? 'success' : 'danger' }} fas {{ $log->recipient_id == $owner->id && $log->recipient_type == $owner->logType ? 'fa-arrow-up' : 'fa-arrow-down' }} me-2"></i>
            {!! $log->sender ? $log->sender->displayName : '' !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {!! $log->recipient ? $log->recipient->displayName : '' !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {{ $log->recipient_id == $owner->id && $log->recipient_type == $owner->logType && $log->quantity > 0 ? '+' : '-' }} {!! $log->currency ? $log->currency->display(abs($log->quantity)) : $log->cost . ' (Deleted Currency)' !!}
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="logs-table-cell">
            {!! $log->log !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {!! pretty_date($log->created_at) !!}
        </div>
    </div>
</div>
