@extends('world.layout')

@section('world-title')
    Rarities
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Rarities' => 'world/rarities']) !!}
    <h1>Rarities</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    {!! $rarities->render() !!}
    @foreach ($rarities as $rarity)
        <div class="card mb-3">
            <div class="card-body">
                @include('world._rarity_entry', [
                    'edit' => [
                        'object' => $rarity,
                        'title' => 'Rarity',
                    ],
                    'imageUrl' => $rarity->rarityImageUrl,
                    'name' => $rarity->displayName,
                    'description' => $rarity->parsed_description,
                    'searchItemsUrl' => $rarity->searchItemsUrl,
                    'searchFeaturesUrl' => $rarity->searchFeaturesUrl,
                    'searchCharactersUrl' => $rarity->searchCharactersUrl,
                ])
            </div>
        </div>
    @endforeach
    {!! $rarities->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $rarities->total() }} result{{ $rarities->total() == 1 ? '' : 's' }} found.</div>
@endsection
