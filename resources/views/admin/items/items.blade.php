@extends('admin.layout')

@section('admin-title')
    Items
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Items' => 'admin/data/items']) !!}

    <h1>Items</h1>

    <p>This is a list of items in the game. Specific details about items can be added when they are granted to users (e.g. reason for grant). By default, items are merely collectibles and any additional functionality must be manually processed, or custom
        coded in for the specific item.</p>

    <div class="text-right mb-3">
        @if (Auth::user()->hasPower('edit_inventories'))
            <a class="btn btn-primary" href="{{ url('admin/grants/item-search') }}"><i class="fas fa-search"></i> Item Search</a>
        @endif
        <a class="btn btn-primary" href="{{ url('admin/data/item-categories') }}"><i class="fas fa-folder"></i> Item Categories</a>
        <a class="btn btn-primary" href="{{ url('admin/data/items/create') }}"><i class="fas fa-plus"></i> Create New Item</a>
    </div>

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
                    'visibility',
                    [
                        'visibleOnly' => 'Released Only',
                        'hiddenOnly' => 'Hidden Only',
                    ],
                    Request::get('visibility'),
                    ['class' => 'form-control', 'placeholder' => 'Any Visibility'],
                ) !!}
            </div>
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
                    Request::get('sort') ?: 'oldest',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($items))
        <p>No items found.</p>
    @else
        {!! $items->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="{{ config('lorekeeper.extensions.item_entry_expansion.extra_fields') ? 'col-6 col-md-5' : 'col-5 col-md-6' }}">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="{{ config('lorekeeper.extensions.item_entry_expansion.extra_fields') ? 'col-6 col-md-3' : 'col-5 col-md-5' }}">
                        <div class="logs-table-cell">Category</div>
                    </div>
                    @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">Rarity</div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($items as $item)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="{{ config('lorekeeper.extensions.item_entry_expansion.extra_fields') ? 'col-6 col-md-5' : 'col-5 col-md-6' }}">
                                <div class="logs-table-cell">
                                    @if (!$item->is_released)
                                        <i class="fas fa-eye-slash mr-1"></i>
                                    @endif
                                    {{ $item->name }}
                                </div>
                            </div>
                            <div class="{{ config('lorekeeper.extensions.item_entry_expansion.extra_fields') ? 'col-6 col-md-3' : 'col-4 col-md-5' }}">
                                <div class="logs-table-cell">{{ $item->category ? $item->category->name : '---' }}</div>
                            </div>
                            @if (config('lorekeeper.extensions.item_entry_expansion.extra_fields'))
                                <div class="col-6 col-md-3">
                                    <div class="logs-table-cell">{!! $item->rarity ? $item->rarity->displayName : '---' !!}</div>
                                </div>
                            @endif
                            <div class="{{ config('lorekeeper.extensions.item_entry_expansion.extra_fields') ? 'col-2 col-md-1' : 'col-3 col-md-1' }} text-right">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/data/items/edit/' . $item->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $items->render() !!}
    @endif
@endsection
