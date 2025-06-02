@extends('home.layout')

@section('home-title')
    Reports
@endsection

@section('home-content')
    {!! breadcrumbs(['Reports' => 'reports']) !!}
    <h1>
        My Reports
    </h1>

    <div class="text-end">
        <a href="{{ url('reports/new') }}" class="btn btn-success">New Report</a>
    </div>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ !Request::get('type') || Request::get('type') == 'pending' ? 'active' : '' }}" href="{{ url('reports') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'assigned' ? 'active' : '' }}" href="{{ url('reports') . '?type=assigned' }}">Assigned</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::get('type') == 'closed' ? 'active' : '' }}" href="{{ url('reports') . '?type=closed' }}">Closed</a>
        </li>
    </ul>

    @if (count($reports))
        {!! $reports->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">Link/Title</div>
                    </div>
                    <div class="col-6 col-md-5">
                        <div class="logs-table-cell">Submitted</div>
                    </div>
                    <div class="col-12 col-md-1">
                        <div class="logs-table-cell">Status</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($reports as $report)
                    <div class="logs-table-row">
                        @include('home._report', ['report' => $report])
                    </div>
                @endforeach
            </div>
        </div>
        {!! $reports->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $reports->total() }} result{{ $reports->total() == 1 ? '' : 's' }} found.</div>
    @else
        <p>No reports found.</p>
    @endif

@endsection
