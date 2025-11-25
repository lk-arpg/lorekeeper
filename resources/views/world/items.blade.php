@extends('world.layout')

@section('world-title')
    Items
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Items' => 'world/items']) !!}
    <h1>Items</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('item_category_id', $categories, Request::get('item_category_id'), ['class' => 'form-control', 'placeholder' => 'Any Category']) !!}
            </div>
            @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                <div class="form-group ml-3 mb-3">
                    {!! Form::select('rarity_id', $rarities, Request::get('rarity_id'), ['class' => 'form-control', 'placeholder' => 'Any Rarity']) !!}
                </div>
                <div class="form-group ml-3 mb-3">
                    {!! Form::select('artist', $artists, Request::get('artist'), ['class' => 'form-control', 'placeholder' => 'Any Artist']) !!}
                </div>
            @endif
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

    {!! $items->render() !!}
    @foreach ($items as $item)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._item_entry', ['item' => $item])
            </div>
        </div>
    @endforeach
    {!! $items->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $items->total() }} result{{ $items->total() == 1 ? '' : 's' }} found.</div>
@endsection
