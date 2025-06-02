@extends('world.layout')

@section('world-title')
    Species
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Species' => 'world/species']) !!}
    <h1>Species</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="mb-3 me-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="mb-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
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
