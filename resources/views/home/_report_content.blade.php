<h1>
    Report (#{{ $report->id }})
    <span class="float-end badge badge-{{ $report->status == 'Pending' ? 'secondary' : ($report->status == 'Closed' ? 'success' : 'danger') }}">{{ $report->status }}</span>
</h1>
<div class="mb-1">
    <div class="row">
        <div class="col-md-2 col-4">
            <h5>User</h5>
        </div>
        <div class="col-md-10 col-8">{!! $report->user->displayName !!}</div>
    </div>
    <div class="row">
        <div class="col-md-2 col-4">
            <h5>URL / Title</h5>
        </div>
        <div class="col-md-10 col-8"><a href="{{ $report->url }}">{{ $report->url }}</a></div>
    </div>
    @if ($report->is_br == 1)
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Bug Type</h5>
            </div>
            <div class="col-md-10 col-8">{{ ucfirst($report->error_type) . ($report->error_type != 'exploit' ? ' Error' : '') }}</div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-2 col-4">
            <h5>Submitted</h5>
        </div>
        <div class="col-md-10 col-8">{!! format_date($report->created_at) !!} ({{ $report->created_at->diffForHumans() }})</div>
    </div>
    @if ($report->status != 'Pending')
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Assigned to</h5>
            </div>
            <div class="col-md-10 col-8">{!! $report->staff->displayName !!} at {!! format_date($report->updated_at) !!} ({{ $report->updated_at->diffForHumans() }})</div>
        </div>
    @endif
</div>

<h2>Report Details</h2>
<div class="card mb-3">
    <div class="card-body">{!! nl2br(htmlentities($report->comments)) !!}</div>
</div>
@if ((Auth::check() && $report->status == 'Assigned' && $report->user_id == Auth::user()->id) || Auth::user()->hasPower('manage_reports'))
    <div class="alert alert-danger">Admins will be alerted by new comments, however to keep the conversation organised we ask that you please reply to the admin comment.</div>
    @comments(['type' => 'Staff-User', 'model' => $report, 'perPage' => 5])
@elseif($report->status == 'Closed')
    <div class="alert alert-danger"> You cannot comment on a closed ticket. </div>
@else
    <div class="alert alert-danger"> Please await admin assignment. </div>
@endif
@if (Auth::check() && $report->staff_comments && ($report->user_id == Auth::user()->id || Auth::user()->hasPower('manage_reports')))
    <h2>Staff Comments</h2>
    <div class="card mb-3">
        <div class="card-body">
            @if (isset($report->parsed_staff_comments))
                {!! $report->parsed_staff_comments !!}
            @else
                {!! $report->staff_comments !!}
            @endif
        </div>
    </div>
@endif
