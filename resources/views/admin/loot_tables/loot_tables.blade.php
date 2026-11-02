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
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end mb-3']) !!}
        <div class="form-group">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select(
                'namespec',
                [
                    'name' => 'Search for Name',
                    'display-name' => 'Search for DisplayName',
                    'both' => 'Search for both',
                ],
                Request::get('namespec') ?: 'name',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Created Newest',
                    'oldest' => 'Created Oldest',
                    'alpha' => 'Sort Alphabetically (A-Z) on Name',
                    'alpha-reverse' => 'Sort Alphabetically (Z-A) on Name',
                    'alpha-dn' => 'Sort Alphabetically (A-Z) on DisplayName',
                    'alpha-dn-reverse' => 'Sort Alphabetically (Z-A) on DisplayName',
                ],
                Request::get('sort') ?: 'oldest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
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
