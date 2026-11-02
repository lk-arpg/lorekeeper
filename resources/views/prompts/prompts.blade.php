@extends('prompts.layout')

@section('prompts-title')
    All Prompts
@endsection

@section('content')
    {!! breadcrumbs(['Prompts' => 'prompts', 'All Prompts' => 'prompts/prompts']) !!}
    <h1>All Prompts</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end mb-3']) !!}
        <div class="form-group">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select('prompt_category_id', $categories, Request::get('prompt_category_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select('open_prompts', ['any' => 'Any Status', 'open' => 'Open Prompts', 'closed' => 'Closed Prompts'], Request::get('open_prompts') ?? 'any', ['class' => 'form-control selectize']) !!}
        </div>
        <div class="form-group ml-3">
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
        <div class="form-group ml-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    {!! $prompts->render() !!}
    @foreach ($prompts as $prompt)
        <div class="card mb-3">
            <div class="card-body">
                @include('prompts._prompt_entry', ['prompt' => $prompt])
            </div>
        </div>
    @endforeach
    {!! $prompts->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $prompts->total() }} result{{ $prompts->total() == 1 ? '' : 's' }} found.</div>
@endsection
