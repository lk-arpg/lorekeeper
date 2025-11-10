@extends('news.layout')

@section('news-title')
    {{ $news->title }}
@endsection

@if ($news->has_image)
    @section('meta-img')
        {{ $news->imageUrl }}
    @endsection
@endif

@section('news-content')
    {!! breadcrumbs(['Site News' => 'news', $news->title => $news->url]) !!}
    @include('news._news', ['news' => $news, 'page' => true])

    <hr class="mb-5" />
    @comments(['model' => $news, 'perPage' => 5])
@endsection
