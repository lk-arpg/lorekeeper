@extends('news.layout')

@section('title')
    Site News
@endsection

@section('news-content')
    {!! breadcrumbs(['Site News' => 'news']) !!}
    <h1>Site News</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end mb-3']) !!}
        <div class="form-group">
            {!! Form::text('title', Request::get('title'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::select(
                'sort',
                [
                    'bump-reverse' => 'Updated Newest',
                    'bump' => 'Updated Oldest',
                    'newest' => 'Created Newest',
                    'oldest' => 'Created Oldest',
                    'alpha' => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                ],
                Request::get('sort') ?: 'Updated Newest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (count($newses))
        {!! $newses->render() !!}
        @foreach ($newses as $news)
            @include('news._news', ['news' => $news, 'page' => false])
        @endforeach
        {!! $newses->render() !!}
    @else
        <div>No news posts yet.</div>
    @endif
@endsection
