<div class="row flex-wrap">
    <div class="col-6 col-md-4">
        <div class="logs-table-cell">
            <span class="ubt-texthide">
                @if (!$report->is_br)
                    <a href="{{ $report->url }}">
                        @endif {{ $report->url }} @if (!$report->is_br)
                    </a>
                @endif
            </span>
        </div>
    </div>
    <div class="col-6 col-md-5">
        <div class="logs-table-cell">{!! pretty_date($report->created_at) !!}</div>
    </div>
    <div class="col-6 col-md-1">
        <div class="logs-table-cell">
            <span class="badge badge-{{ $report->status == 'Pending' ? 'secondary' : ($report->status == 'Closed' ? 'success' : 'danger') }}">{{ $report->status }}</span>
        </div>
    </div>
    <div class="col-6 col-md-2 text-end">
        <div class="logs-table-cell">
            @if ($report->status == 'Closed' || ($report->status == 'Assigned' && $report->is_br && $report->error_type != 'exploit') || (Auth::check() && Auth::user()->id == $report->user_id))
                <a href="{{ $report->viewUrl }}" class="btn btn-primary btn-sm">Details</a>
            @else
                <a class="btn btn-dark btn-sm text-light">Report not closed</a>
            @endif
        </div>
    </div>
</div>
