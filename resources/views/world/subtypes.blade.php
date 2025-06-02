@extends('world.layout')

@section('world-title')
    Subtypes
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Subtypes' => 'world/subtypes']) !!}
    <h1>Subtypes</h1>

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
