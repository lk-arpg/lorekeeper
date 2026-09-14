@extends('world.layout')

@section('world-title')
    {{ $criterion->name }} Criterion
@endsection

@section('world-content')
    {!! breadcrumbs(['World' => 'world', 'Criteria Guides' => 'world/criteria-guides', $criterion->name => 'world/criteria-guides/' . $criterion->id]) !!}

    @include('criteria._guide', ['criterion' => $criterion])
@endsection
