@extends('admin.layout')

@section('admin-title')
    Queue
@endsection

@section('admin-content')
        {!! breadcrumbs(['Admin Panel' => 'admin', 'Queue Submissions' => 'admin/queue-submissions/pending']) !!}

    <h1>
        Queue Submissions
    </h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/queue-submissions/pending*') }} {{ set_active('admin/queue-submissions') }}"
                href="{{ url('admin/queue-submissions/pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/queue-submissions/approved*') }}" href="{{ url('admin/queue-submissions/approved') }}">Approved</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/queue-submissions/rejected*') }}" href="{{ url('admin/queue-submissions/rejected') }}">Rejected</a>
        </li>
    </ul>

    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select('queue_category_id', $categories, Request::get('queue_category_id'), ['class' => 'form-control']) !!}
            </div>
    </div>
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'oldest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    {!! $submissions->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                    <div class="col-12 col-md-2">
                        <div class="logs-table-cell">Queue</div>
                    </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">User</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Submitted</div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="logs-table-cell">Status</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($submissions as $submission)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                            <div class="col-12 col-md-2">
                                <div class="logs-table-cell">{!! $submission->queue->displayName !!}</div>
                            </div>
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">{!! $submission->user->displayName !!}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{!! pretty_date($submission->created_at) !!}</div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell">
                                <span class="btn btn-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $submission->status }}</span>
                            </div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell"><a href="{{ $submission->adminUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $submissions->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $submissions->total() }} result{{ $submissions->total() == 1 ? '' : 's' }} found.</div>
@endsection
