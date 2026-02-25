@extends('queues.layout')

@section('queues-title')
    All Queues
@endsection

@section('content')
    {!! breadcrumbs(['Queues' => 'queues', 'All Queues' => 'queues/queues']) !!}
    <h1>All Queues</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('queue_category_id', $categories, Request::get('queue_category_id'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('open_queues', ['any' => 'Any Status', 'open' => 'Open Queues', 'closed' => 'Closed Queues'], Request::get('open_queues') ?? 'any', ['class' => 'form-control selectize']) !!}
            </div>
            <div class="form-inline justify-content-end">
                <div class="form-group ml-3 mb-3">
                    {!! Form::select(
                        'sort',
                        [
                            'alpha' => 'Sort Alphabetically (A-Z)',
                            'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                            'category' => 'Sort by Category',
                            'newest' => 'Newest First',
                            'oldest' => 'Oldest First',
                            'start' => 'Starts Earliest',
                            'start-reverse' => 'Starts Latest',
                            'end' => 'Ends Earliest',
                            'end-reverse' => 'Ends Latest',
                        ],
                        Request::get('sort') ?: 'category',
                        ['class' => 'form-control'],
                    ) !!}
                </div>
                <div class="form-group ml-3 mb-3">
                    {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        {!! $queues->render() !!}
        @foreach ($queues as $queue)
            <div class="card mb-3">
                <div class="card-body">
                    @include('queues._queue_entry', ['queue' => $queue])
                </div>
            </div>
        @endforeach
        {!! $queues->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $queues->total() }} result{{ $queues->total() == 1 ? '' : 's' }} found.</div>
    @endsection
