@extends('world.layout')

@section('world-title')
    Item Categories
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Item Categories' => 'world/item-categories']) !!}
    <h1>Item Categories</h1>

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

    {!! $categories->render() !!}
    @foreach ($categories as $category)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._item_category_entry', [
                    'imageUrl' => $category->categoryImageUrl,
                    'name' => $category->displayName,
                    'description' => $category->parsed_description,
                    'searchUrl' => $category->searchUrl,
                    'category' => $category,
                ])
            </div>
        </div>
    @endforeach
    {!! $categories->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $categories->total() }} result{{ $categories->total() == 1 ? '' : 's' }} found.</div>
@endsection
