@extends('layouts.app')

@section('title')
    Feeds
@endsection

@section('content')
    {!! breadcrumbs(['Feeds' => url('feeds')]) !!}
    <h1>RSS Feeds</h1>
    <p class="mb-2">RSS feeds are automatically-updating files containing blog posts, news updates, or other content that you can keep track of using an RSS feed reader of your choice. To keep up with any of the feeds below, simply paste their link
        where your RSS reader prompts you!</p>

    <hr>

    <h4 class="mb-0"><a href="{{ url('feeds/news') }}"><i class="fas fa-rss-square"></i> News Feed</a> <i data-toggle="tooltip" title="Click to Copy the URL" data-copy="{{ url('feeds/news') }}" class="far fa-copy text-small"></i></h4>
    <p class="mb-2">Updates with recent News posts on this site.</p>
    <p class="mb-2"><code>{{ url('feeds/news') }}</code></p>

    <br>

    <h4 class="mb-0"><a href="{{ url('feeds/sales') }}"><i class="fas fa-rss-square"></i> Sales Feed</a> <i data-toggle="tooltip" title="Click to Copy the URL" data-copy="{{ url('feeds/sales') }}" class="far fa-copy text-small"></i></h4>
    <p class="mb-2">Updates with recent Sales posts on this site.</p>
    <p class="mb-2"><code>{{ url('feeds/sales') }}</code></p>
@endsection
@section('scripts')
    <script>
        $('.fa-copy').on('click', async (e) => {
            await window.navigator.clipboard.writeText(e.currentTarget.dataset.copy);
            e.currentTarget.classList.remove('toCopy');
            e.currentTarget.classList.add('toCheck');
            setTimeout(() => {
                e.currentTarget.classList.remove('toCheck');
                e.currentTarget.classList.add('toCopy');
            }, 2000);
        });
    </script>
@endsection
