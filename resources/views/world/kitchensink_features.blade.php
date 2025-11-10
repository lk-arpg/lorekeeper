@extends('world.layout')

@section('title')
    All Traits Index
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'All Traits Index' => 'world/all-traits-index']) !!}
    <h1>All Traits Index</h1>

    <p>This is a visual index of all traits. Click a trait to view more info on it!</p>

    @include('world._features_index', ['features' => $features, 'showSubtype' => true])
@endsection

@section('scripts')
    @if (config('lorekeeper.extensions.visual_trait_index.trait_modals'))
        @include('world._features_index_modal_js')
    @endif
@endsection
