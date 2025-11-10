@extends('admin.layout')

@section('admin-title')
    Loot Tables
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Loot Tables' => 'admin/data/loot-tables']) !!}

    <h1>Loot Tables</h1>

    <p>Loot tables can be attached to prompts as a reward for doing the prompt. This will roll a random reward from the contents of the table. Tables can be chained as well.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/loot-tables/create') }}"><i class="fas fa-plus"></i> Create New Loot Table</a></div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($tables))
        <p>No loot tables found.</p>
    @else
        {!! $tables->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-3 col-md-2">
                        <div class="logs-table-cell">ID</div>
                    </div>
                    <div class="col-3 col-md-4">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-6 col-md-5">
                        <div class="logs-table-cell">Display Name</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($tables as $table)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-3 col-md-2">
                                <div class="logs-table-cell">#{{ $table->id }}</div>
                            </div>
                            <div class="col-3 col-md-4">
                                <div class="logs-table-cell">{{ $table->name }}</div>
                            </div>
                            <div class="col-3 col-md-5">
                                <div class="logs-table-cell">{!! $table->display_name !!}</div>
                            </div>
                            <div class="col-3 col-md-1 text-right">
                                <div class="logs-table-cell"><a href="{{ url('admin/data/loot-tables/edit/' . $table->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $tables->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $tables->total() }} result{{ $tables->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection
