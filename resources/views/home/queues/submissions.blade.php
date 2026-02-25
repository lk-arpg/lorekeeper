@extends('home.layout')

@section('home-title')
    Queue Submissions
@endsection

@section('home-content')

    {!! breadcrumbs(['Queue Submissions' => 'queue-submissions']) !!}

    @if (isset($queue))
        <div class="float-right">
            <a href="{{ url('queue-submissions/new/' . $queue->id) }}" class="btn btn-success">New Submission</a>
        </div>
    @endif

    <h1>
        {{ isset($queue) ? $queue->name : 'Queue' }} Submissions
    </h1>



    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'draft' ? 'active' : '' }}" href="/queue-submissions{{ isset($queue) ? '/' . $queue->id : '' }}?type=draft">Drafts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ !Request::get('type') || Request::get('type') == 'pending' ? 'active' : '' }}" href="/queue-submissions{{ isset($queue) ? '/' . $queue->id : '' }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'approved' ? 'active' : '' }}" href="/queue-submissions{{ isset($queue) ? '/' . $queue->id : '' }}?type=approved">Approved</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'rejected' ? 'active' : '' }}" href="/queue-submissions{{ isset($queue) ? '/' . $queue->id : '' }}?type=rejected">Rejected</a>
        </li>
    </ul>

    @if (count($submissions))
        {!! $submissions->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    @if (!isset($queue))
                        <div class="col-12 col-md-2 font-weight-bold">
                            <div class="logs-table-cell">Queue</div>
                        </div>
                    @endif
                    <div class="col-6 col-md-5 font-weight-bold">
                        <div class="logs-table-cell">Last Action</div>
                    </div>
                    <div class="col-12 col-md-1 font-weight-bold">
                        <div class="logs-table-cell">Status</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($submissions as $submission)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            @if (!isset($queue))
                                <div class="col-12 col-md-2">
                                    <div class="logs-table-cell">{!! $submission->queue->displayName !!}</div>
                                </div>
                            @endif
                            <div class="col-6 col-md-5">
                                <div class="logs-table-cell">{!! pretty_date($submission->updated_at) !!}</div>
                            </div>
                            <div class="col-6 col-md-1 text-right">
                                <div class="logs-table-cell">
                                    <span class="btn btn-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $submission->status }}</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-1">
                                <div class="logs-table-cell"><a href="{{ $submission->viewUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $submissions->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $submissions->total() }} result{{ $submissions->total() == 1 ? '' : 's' }} found.</div>
    @else
        <p>No submissions found.</p>
    @endif

@endsection
