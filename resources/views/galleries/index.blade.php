@extends('galleries.layout')

@section('gallery-title')
    Home
@endsection

@section('gallery-content')
    {!! breadcrumbs(['Gallery' => 'gallery']) !!}
    <h1>
        @if (config('lorekeeper.extensions.show_all_recent_submissions.enable') && config('lorekeeper.extensions.show_all_recent_submissions.links.indexbutton'))
            <div class="float-right">
                <a class="btn btn-primary" href="gallery/all">
                    All Recent Submissions
                </a>
            </div>
        @endif
        Gallery
    </h1>

    @if ($galleries->count())
        {!! $galleries->render() !!}

        @foreach ($galleries as $gallery)
            <div class="card mb-4">
                <div class="card-header">
                    <h4>
                        {!! $gallery->displayName !!}
                        @if (Auth::check() && $gallery->canSubmit($submissionsOpen, Auth::user()))
                            <a href="{{ url('gallery/submit/' . $gallery->id) }}" class="btn btn-primary float-right"><i class="fas fa-plus"></i></a>
                        @endif
                    </h4>
                    @if ($gallery->children_count || (isset($gallery->start_at) || isset($gallery->end_at)))
                        <p>
                            @if (isset($gallery->start_at) || isset($gallery->end_at))
                                @if ($gallery->start_at)
                                    <strong>Open{{ $gallery->start_at->isFuture() ? 's' : 'ed' }}: </strong>{!! pretty_date($gallery->start_at) !!}
                                @endif
                                {{ $gallery->start_at && $gallery->end_at ? ' ・ ' : '' }}
                                @if ($gallery->end_at)
                                    <strong>Close{{ $gallery->end_at->isFuture() ? 's' : 'd' }}: </strong>{!! pretty_date($gallery->end_at) !!}
                                @endif
                            @endif
                            {{ $gallery->children_count && (isset($gallery->start_at) || isset($gallery->end_at)) ? ' ・ ' : '' }}
                            @if ($gallery->children_count)
                                Sub-galleries:
                                @foreach ($gallery->children as $child)
                                    {!! $child->displayName !!}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            @endif
                        </p>
                    @endif
                </div>
                <div class="card-body">
                    @if ($gallery->submissions_count)
                        <div class="row">
                            @foreach ($gallery->submissions()->with('collaborators', 'participants')->withDisplayData(Auth::user() ?? null)->limit(4)->get() as $submission)
                                <div class="col-md-3 text-center align-self-center">
                                    @include('galleries._thumb', ['submission' => $submission, 'gallery' => true])
                                </div>
                            @endforeach
                        </div>
                        @if ($gallery->submissions_count > 4)
                            <div class="text-right"><a href="{{ url('gallery/' . $gallery->id) }}">See More...</a></div>
                        @endif
                    @elseif($gallery->children_count && $gallery->childSubmissions()->exists())
                        <div class="row">
                            @foreach ($gallery->childSubmissions()->with('gallery', 'collaborators', 'participants')->withDisplayData(Auth::user() ?? null)->limit(4)->get() as $submission)
                                <div class="col-md-3 text-center align-self-center">
                                    @include('galleries._thumb', ['submission' => $submission, 'gallery' => false])
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p>This gallery has no submissions!</p>
                    @endif
                </div>
            </div>
        @endforeach

        {!! $galleries->render() !!}
    @else
        <p>There aren't any galleries!</p>
    @endif

@endsection
