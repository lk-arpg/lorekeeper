@extends('world.layout')

@section('world-title')
    Home
@endsection

@section('content')
    {!! breadcrumbs(['Encyclopedia' => 'world']) !!}

    <h1>World</h1>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ asset('images/characters.png') }}" alt="Characters" />
                    <h5 class="card-title">Characters</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ url('world/species') }}">Species</a></li>
                    <li class="list-group-item"><a href="{{ url('world/subtypes') }}">Subtypes</a></li>
                    <li class="list-group-item"><a href="{{ url('world/rarities') }}">Rarities</a></li>
                    <li class="list-group-item"><a href="{{ url('world/trait-categories') }}">Trait Categories</a></li>
                    <li class="list-group-item"><a href="{{ url('world/traits') }}">All Traits</a></li>
                    @if (config('lorekeeper.extensions.visual_trait_index.enable_universal_index'))
                        <li class="list-group-item"><a href="{{ url('world/universaltraits') }}">Universal Trait Index</a></li>
                    @endif
                    @if (config('lorekeeper.extensions.visual_trait_index.enable_all_trait_index'))
                        <li class="list-group-item"><a href="{{ url('world/all-traits-index') }}">All Traits Index</a></li>
                    @endif
                    <li class="list-group-item"><a href="{{ url('world/character-categories') }}">Character Categories</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ asset('images/inventory.png') }}" alt="Items" />
                    <h5 class="card-title">Items</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ url('world/item-categories') }}">Item Categories</a></li>
                    <li class="list-group-item"><a href="{{ url('world/items') }}">All Items</a></li>
                    <li class="list-group-item"><a href="{{ url('world/currencies') }}">Currencies</a></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
