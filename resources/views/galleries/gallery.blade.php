@extends('galleries.layout')

@section('gallery-title')
    {{ $gallery->name }}
@endsection

@section('gallery-content')
    <x-admin-edit title="Gallery" :object="$gallery" />
    {!! breadcrumbs(['Gallery' => 'gallery', $gallery->name => 'gallery/' . $gallery->id]) !!}

    <h1>
        {{ $gallery->name }}
        @if (Auth::check() && $gallery->canSubmit(Settings::get('gallery_submissions_open'), Auth::user()))
            <a href="{{ url('gallery/submit/' . $gallery->id) }}" class="btn btn-primary float-end"><i class="fas fa-plus me-1"></i> Submit</a>
        @endif
    </h1>
    @if (isset($gallery->start_at) || isset($gallery->end_at))
        <p>
            @if ($gallery->start_at)
                <strong>Open{{ $gallery->start_at->isFuture() ? 's' : 'ed' }}: </strong>{!! pretty_date($gallery->start_at) !!}
            @endif
            {{ $gallery->start_at && $gallery->end_at ? ' ・ ' : '' }}
            @if ($gallery->end_at)
                <strong>Close{{ $gallery->end_at->isFuture() ? 's' : 'd' }}: </strong>{!! pretty_date($gallery->end_at) !!}
            @endif
        </p>
    @endif
    <p>{!! $gallery->description !!}</p>
    @if (!$gallery->submissions_count && $gallery->children->count() && $childSubmissions->count())
        <p>This gallery has no submissions; instead, displayed is a selection of the most recent submissions from its sub-galleries. Please navigate to one of the sub-galleries to view more.</p>
    @endif

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="mb-3 me-3 mb-3">
            {!! Form::text('title', Request::get('title'), ['class' => 'form-control', 'placeholder' => 'Title']) !!}
        </div>
        <div class="mb-3 me-3 mb-3">
            {!! Form::select('prompt_id', $prompts, Request::get('prompt_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="mb-3 me-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                    'alpha' => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                    'prompt' => 'Sort by Prompt (Newest to Oldest)',
                    'prompt-reverse' => 'Sort by Prompt (Oldest to Newest)',
                ],
                Request::get('sort') ?: 'category',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="mb-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if ($gallery->submissions_count)
        {!! $submissions->render() !!}

        <div class="d-flex align-content-around flex-wrap mb-2">
            @foreach ($submissions as $submission)
                @include('galleries._thumb', ['submission' => $submission, 'gallery' => true])
            @endforeach
        </div>

        {!! $submissions->render() !!}
    @elseif($childSubmissions->count())
        <div class="d-flex align-content-around flex-wrap mb-2">
            @foreach ($childSubmissions->orderBy('created_at', 'DESC')->get()->take(20) as $submission)
                @include('galleries._thumb', ['submission' => $submission, 'gallery' => false])
            @endforeach
        </div>
    @else
        <p>No submissions found!</p>
    @endif

@endsection
