@extends('layouts.app')

@section('title')
    Bug Reports
@endsection

@section('content')
    {!! breadcrumbs(['Reports' => 'reports']) !!}
    <h1>
        Bug Reports
    </h1>

    <p>Please check the current 'fix in progress' reports to ensure your bug is not already being worked on! If the title is not descriptive enough, or does not match your bug, feel free to create a new one.</p>
    <div class="alert alert-warning">Please note that certain bug reports cannot be viewed until they are closed to prevent abuse.</div>

    @if (Auth::check())
        <div class="text-end">
            <a href="{{ url('reports/new') }}" class="btn btn-success">New Report</a>
        </div>
    @endif
    <br>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="mb-3 me-3 mb-3">
        {!! Form::text('url', Request::get('url'), ['class' => 'form-control', 'placeholder' => 'URL / Title']) !!}
    </div>
    <div class="mb-3 mb-3">
        {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}

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
