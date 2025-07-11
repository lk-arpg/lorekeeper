@extends('layouts.app')

@section('title')
    {{ $page->title }}
@endsection

@if ($page->has_image)
    @section('meta-img')
        {{ $page->imageUrl }}
    @endsection
@endif

@section('content')
    <x-admin-edit title="Page" :object="$page" />
    {!! breadcrumbs([$page->title => $page->url]) !!}
    <h1>
        @if (!$page->is_visible)
            <i class="fas fa-eye-slash mr-1"></i>
        @endif
        {{ $page->title }}
    </h1>
    <div class="mb-4">
        <div><strong>Created:</strong> {!! format_date($page->created_at) !!}</div>
        <div><strong>Last updated:</strong> {!! format_date($page->updated_at) !!}</div>
    </div>

    @if ($page->has_image)
        <div class="page-image">
            <img src="{{ $page->imageUrl }}" alt="{{ $page->name }}" class="w-100" />
        </div>
    @endif
    <div class="site-page-content parsed-text">
        {!! $page->parsed_text !!}
    </div>

    @if ($page->can_comment)
        <div class="container">
            @comments([
                'model' => $page,
                'perPage' => 5,
                'allow_dislikes' => $page->allow_dislikes,
            ])
        </div>
    @endif
@endsection
