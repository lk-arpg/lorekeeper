@extends('world.layout')

@section('world-title')
    Universal Trait Index
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Universal Trait Index' => 'world/universaltraits']) !!}
    <h1>Universal Trait Index</h1>

    <p>This is a visual index of all universal traits. Click a trait to view more info on it!</p>

    @include('world._features_index', ['features' => $features, 'showSubtype' => false])
@endsection

@section('scripts')
    @if (config('lorekeeper.extensions.visual_trait_index.trait_modals'))
        @include('world._features_index_modal_js')
    @endif
@endsection
