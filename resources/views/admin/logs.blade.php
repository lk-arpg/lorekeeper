@extends('admin.layout')

@section('admin-title')
    Logs
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Logs' => 'admin/logs']) !!}

    <h1>Admin Logs</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="mb-3 me-3 mb-3">
            {!! Form::select('action', $actions, Request::get('action'), ['class' => 'form-control', 'placeholder' => 'Select Action']) !!}
        </div>
        <div class="mb-3 me-3 mb-3">
            {!! Form::select('user_id', $staff, Request::get('user_id'), ['class' => 'form-control', 'placeholder' => 'Select Staff']) !!}
        </div>
        <div class="mb-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    {!! $logs->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Staff</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Action</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Action Details</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($logs as $log)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{!! $log->user->displayName !!}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{!! $log->action !!}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{!! $log->action_details !!}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">{!! format_date($log->created_at) !!} ({!! pretty_date($log->created_at) !!})</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $logs->render() !!}
@endsection
