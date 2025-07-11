@extends('prompts.layout')

@section('title')
    {{ $prompt->name }}
@endsection

@if ($prompt->has_image)
    @section('meta-img')
        {{ $prompt->imageUrl }}
    @endsection
@endif

@section('content')
    {!! breadcrumbs(['Prompts' => 'prompts', 'All Prompts' => 'prompts/prompts', $prompt->name => 'prompts/' . $prompt->id]) !!}
    @include('prompts._prompt_entry', ['prompt' => $prompt, 'isPage' => true])
@endsection
