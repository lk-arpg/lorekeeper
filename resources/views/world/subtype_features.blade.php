@extends('world.layout')

@section('world-title')
    {{ $subtype->name }} ({!! $subtype->species->name !!} Subtype) Traits
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'Subtypes' => 'world/subtypes', $subtype->name => $subtype->url, 'Traits' => 'world/subtypes/' . $subtype->id . 'traits']) !!}
    <h1>{{ $subtype->name }} ({!! $subtype->species->name !!} Subtype) Traits</h1>

    <p>This is a visual index of all {!! $subtype->displayName !!} ({!! $subtype->species->displayName !!} Subtype)-specific traits. Click a trait to view more info on it!</p>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::checkbox('add_basics', 1, Request::get('add_basics'), ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('add_basics', 'Add Species traits', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is on, other traits from the species that lack a subtype will be added to the page.') !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Refresh', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    @include('world._features_index', ['features' => $features, 'showSubtype' => false])
@endsection

@section('scripts')
    @if (config('lorekeeper.extensions.visual_trait_index.trait_modals'))
        @include('world._features_index_modal_js')
    @endif
@endsection
