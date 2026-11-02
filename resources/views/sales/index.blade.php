@extends('sales.layout')

@section('title')
    Site Sales
@endsection

@section('sales-content')
    {!! breadcrumbs(['Site Sales' => 'sales']) !!}
    <h1>Site Sales</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end mb-3']) !!}
        <div class="form-group">
            {!! Form::text('title', Request::get('title'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select('is_open', ['1' => 'Open', '0' => 'Closed'], Request::get('is_open'), ['class' => 'form-control', 'placeholder' => 'Status']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select(
                'sort',
                [
                    'bump-reverse' => 'Updated Newest',
                    'bump' => 'Updated Oldest',
                    'newest' => 'Created Newest',
                    'oldest' => 'Created Oldest',
                    'alpha' => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                ],
                Request::get('sort') ?: 'bump-reverse',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (count($saleses))
        {!! $saleses->render() !!}
        @foreach ($saleses as $sales)
            @include('sales._sales', ['sales' => $sales, 'page' => false])
        @endforeach
        {!! $saleses->render() !!}
    @else
        <div>No sales posts yet.</div>
    @endif
@endsection
