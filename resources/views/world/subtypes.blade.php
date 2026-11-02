@extends('world.layout')

@section('world-title')
    Subtypes
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Subtypes' => 'world/subtypes']) !!}
    <h1>Subtypes</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('species_id', $specieses, Request::get('species_id'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'standard' => 'Default Sorting',
                        'standard-reverse' => 'Default Sorting (Reverse)',
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'species' => 'Sort by Species',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'standard',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $subtypes->render() !!}
    @foreach ($subtypes as $subtype)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._subtype_entry', ['subtype' => $subtype])
            </div>
        </div>
    @endforeach
    {!! $subtypes->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $subtypes->total() }} result{{ $subtypes->total() == 1 ? '' : 's' }} found.</div>
@endsection
