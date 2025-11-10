@extends('world.layout')

@section('world-title')
    Species
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Species' => 'world/species']) !!}
    <h1>Species</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'standard' => 'Default Sorting',
                        'standard-reverse' => 'Default Sorting (Reverse)',
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
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

    {!! $specieses->render() !!}
    @foreach ($specieses as $species)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._species_entry', ['species' => $species])
            </div>
        </div>
    @endforeach
    {!! $specieses->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $specieses->total() }} result{{ $specieses->total() == 1 ? '' : 's' }} found.</div>
@endsection
