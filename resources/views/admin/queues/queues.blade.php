@extends('admin.layout')

@section('admin-title')
    Queues
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Queues' => 'admin/data/queues']) !!}

    <h1>Queues</h1>

    <p>This is a list of queues users can submit to.</p>
    <p>Queues can be submitted to vanilla, but are primarily designed to hook into/have additional functions with other extensions or custom code.</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/queue-categories') }}"><i class="fas fa-folder"></i> Queue Categories</a>
        <a class="btn btn-primary" href="{{ url('admin/data/queues/create') }}"><i class="fas fa-plus"></i> Create New Queue</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('queue_category_id', $categories, Request::get('queue_category_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">{!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}</div>
        {!! Form::close() !!}
    </div>

    @if (!count($queues))
        <p>No queues found.</p>
    @else
        {!! $queues->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-4 col-md-1">
                        <div class="logs-table-cell">Active</div>
                    </div>
                    <div class="col-4 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-4 col-md-3">
                        <div class="logs-table-cell">Category</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="logs-table-cell">Starts</div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="logs-table-cell">Ends</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($queues as $queue)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-2 col-md-1">
                                <div class="logs-table-cell">
                                    {!! $queue->is_active ? '<i class="text-success fas fa-check"></i>' : '' !!}
                                </div>
                            </div>
                            <div class="col-5 col-md-3 text-truncate">
                                <div class="logs-table-cell">
                                    {{ $queue->name }}
                                </div>
                            </div>
                            <div class="col-5 col-md-3">
                                <div class="logs-table-cell">
                                    {{ $queue->category ? $queue->category->name : '-' }}
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="logs-table-cell">
                                    {!! $queue->start_at ? pretty_date($queue->start_at) : '-' !!}
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="logs-table-cell">
                                    {!! $queue->end_at ? pretty_date($queue->end_at) : '-' !!}
                                </div>
                            </div>
                            <div class="col-3 col-md-1 text-right">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/data/queues/edit/' . $queue->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {!! $queues->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $queues->total() }} result{{ $queues->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
