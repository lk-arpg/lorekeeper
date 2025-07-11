@extends('world.layout')

@section('world-title')
    Currencies
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Currencies' => 'world/currencies']) !!}
    <h1>Currencies</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('currency_category_id', $categories, Request::get('currency_category_id'), ['class' => 'form-control', 'placeholder' => 'Any Category']) !!}
            </div>
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

    {!! $currencies->render() !!}
    @foreach ($currencies as $currency)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._currency_entry', ['currency' => $currency])
            </div>
        </div>
    @endforeach
    {!! $currencies->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $currencies->total() }} result{{ $currencies->total() == 1 ? '' : 's' }} found.</div>
@endsection
