@extends('galleries.layout')

@section('gallery-title')
    {{ $submission->displayTitle }}
@endsection

@section('meta-img')
    {{ isset($submission->hash) ? $submission->thumbnailUrl : asset('images/meta-image.png') }}
@endsection

@section('gallery-content')
    {!! breadcrumbs(['gallery' => 'gallery', $submission->gallery->displayName => 'gallery/' . $submission->gallery->id, $submission->displayTitle => 'gallery/view/' . $submission->id]) !!}

    <h1>
        @if (!$submission->isVisible)
            <i class="fas fa-eye-slash"></i>
        @endif {{ $submission->displayTitle }}
        <div class="float-end">
            @if (Auth::check())
                {!! Form::open(['url' => '/gallery/favorite/' . $submission->id]) !!}
                @if ($submission->user->id != Auth::user()->id && $submission->collaborators->where('user_id', Auth::user()->id)->first() == null && $submission->isVisible)
                    {!! Form::button('<i class="fas fa-star"></i> ', [
                        'class' => 'btn ' . ($submission->favorites->where('user_id', Auth::user()->id)->first() == null ? 'btn-outline-primary' : 'btn-primary'),
                        'data-toggle' => 'tooltip',
                        'title' => ($submission->favorites->where('user_id', Auth::user()->id)->first() == null ? 'Add to' : 'Remove from') . ' your Favorites',
                        'type' => 'submit',
                    ]) !!}
                @endif
                @if ($submission->user->id == Auth::user()->id || Auth::user()->hasPower('manage_submissions'))
                    <a class="btn btn-outline-primary" href="/gallery/queue/{{ $submission->id }}" data-bs-toggle="tooltip" title="View Log Details"><i class="fas fa-clipboard-list"></i></a>
                    @if ($submission->status != 'Rejected')
                        <a class="btn btn-outline-primary" href="/gallery/edit/{{ $submission->id }}"><i class="fas fa-edit"></i> Edit</a>
                    @endif
                @endif
                {!! Form::close() !!}
            @endif
        </div>
    </h1>
    <div class="mb-3 mb-sm-4">
        <div class="row">
            <div class="col-md">
                In {!! $submission->gallery->displayName !!} ・
                By {!! $submission->credits !!}
            </div>
            <div class="col-md text-end">
                {{ $submission->favorites_count }} Favorite{{ $submission->favorites_count != 1 ? 's' : '' }} ・ {{ $submission->comments->where('type', 'User-User')->count() }}
                Comment{{ $submission->comments->where('type', 'User-User')->count() != 1 ? 's' : '' }}
            </div>
        </diV>
    </div>

    <!-- Main Content -->
    @if (isset($submission->parsed_text) && $submission->parsed_text)
        <div class="card mx-md-4 mb-4">
            <div class="card-body">
    @endif
    @if (isset($submission->hash) && $submission->hash)
        <div class="text-center mb-4">
            <a href="{{ $submission->imageUrl }}" data-lightbox="entry" data-bs-title="{{ $submission->displayTitle }}">
                <img src="{{ $submission->imageUrl }}" class="image" style="max-width:100%; {{ isset($submission->parsed_text) && $submission->parsed_text ? 'max-height:50vh;' : 'max-height:70vh;' }} border-radius:.5em;" data-bs-toggle="tooltip"
                    title="Click to view larger size" alt="{{ $submission->displayTitle }}" />
            </a>
        </div>
    @endif
    @if (isset($submission->parsed_text) && $submission->parsed_text)
        {!! $submission->parsed_text !!}
    @endif
    @if (isset($submission->parsed_text) && $submission->parsed_text)
        </div>
        </div>
    @endif

    <!-- Submission Info -->
    <div class="row mx-md-2 mb-4">
        <div class="col-md mb-4">
            <div class="row mb-4 g-0">
                <div class="col-md-2 mb-4 mobile-hide text-center">
                    <a href="/user/{{ $submission->user->name }}"><img src="{{ $submission->user->avatarUrl }}" style="border-radius:50%; margin-right:25px; max-width:100%;" data-bs-toggle="tooltip" title="{{ $submission->user->name }}"
                            alt="{{ $submission->user->name }}'s Avatar" /></a>
                </div>
                <div class="col-md ms-md-2">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ $submission->displayTitle }}
                                <a class="float-end" href="{{ url('reports/new?url=') . $submission->url }}"><i class="fas fa-exclamation-triangle" data-bs-toggle="tooltip" title="Click here to report this submission." style="opacity: 50%;"></i></a>
                            </h5>
                            <div class="float-end">
                                @if (Auth::check() && ($submission->user->id != Auth::user()->id && $submission->collaborators->where('user_id', Auth::user()->id)->first() == null) && $submission->isVisible)
                                    {!! Form::open(['url' => '/gallery/favorite/' . $submission->id]) !!}
                                    {{ $submission->favorites->count() }} {!! Form::button('<i class="fas fa-star"></i> ', [
                                        'style' => 'border:0; border-radius:.5em;',
                                        'class' => $submission->favorites->where('user_id', Auth::user()->id)->first() != null ? 'btn-success' : '',
                                        'data-toggle' => 'tooltip',
                                        'title' => ($submission->favorites->where('user_id', Auth::user()->id)->first() == null ? 'Add to' : 'Remove from') . ' your Favorites',
                                        'type' => 'submit',
                                    ]) !!} ・ {{ $submission->comments->where('type', 'User-User')->count() }} <i class="fas fa-comment"></i>
                                    {!! Form::close() !!}
                                @else
                                    {{ $submission->favorites->count() }} <i class="fas fa-star" data-bs-toggle="tooltip" title="Favorites"></i> ・ {{ $submission->comments->where('type', 'User-User')->count() }} <i class="fas fa-comment"
                                        data-bs-toggle="tooltip" title="Comments"></i>
                                @endif
                            </div>
                            In {!! $submission->gallery->displayName !!} ・ By {!! $submission->credits !!}
                            @if (isset($submission->content_warning))
                                ・ <span class="text-danger"><strong>Content Warning:</strong></span> {!! nl2br(htmlentities($submission->content_warning)) !!}
                            @endif
                        </div>
                        <div class="card-body">
                            {!! $submission->parsed_description ? $submission->parsed_description : '<i>No description provided.</i>' !!}

                            <hr />
                            <p>
                                <strong>Submitted By</strong> {!! $submission->user->displayName !!}
                                @if ($submission->prompt_id)
                                    <strong>for</strong> {!! $submission->prompt->displayName !!}
                                @endif
                                @if ($submission->favorites->count())
                                    ・ <a class="view-favorites" href="#">View Favorites</a>
                                @endif
                                <br />
                                <strong>Submitted:</strong> {!! pretty_date($submission->created_at) !!} ・
                                <strong>Last Updated:</strong> {!! pretty_date($submission->updated_at) !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            @if ($submission->collaborators->count())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Collaborators</h5>
                    </div>
                    <div class="card-body">
                        @if ($submission->status == 'Pending' && Auth::check() && $submission->collaborators->where('user_id', Auth::user()->id)->first() != null)
                            <p>Check that your role in the collaboration is correct as listed, and if not, make any changes. You can also remove yourself from the collaborator list if necessary. When you are done, or if the record is already accurate,
                                press "submit" to make any changes and mark yourself as having approved. You will be able to edit this until the submission is approved.</p>
                            {!! Form::open(['url' => '/gallery/collaborator/' . $submission->id]) !!}
                            @foreach ($submission->collaborators as $collaborator)
                                @if ($collaborator->user_id == Auth::user()->id)
                                    <div class="mb-2">
                                        <div class="d-flex">{!! $collaborator->has_approved ? '<div class="mb-2 me-2 text-success" data-bs-toggle="tooltip" title="Has Approved"><i class="fas fa-check"></i></div>' : '' !!}{!! $collaborator->user->displayName !!}:
                                        </div>
                                        <div class="d-flex">
                                            {!! Form::text('collaborator_data[]', $collaborator->data, ['class' => 'form-control me-2', 'placeholder' => 'Role (Sketch, Lines, etc.)']) !!}
                                        </div>
                                        {!! Form::label('remove_user', 'Remove Me', ['class' => 'form-check-label ms-3']) !!} {!! add_help('If toggled on, this will remove the record of your collaboration from this submission.') !!}
                                        {!! Form::checkbox('remove_user', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-onstyle' => 'danger']) !!}
                                    </div>
                                @else
                                    <div class="d-flex">
                                        {!! $collaborator->has_approved ? '<div class="mb-2 me-2 text-success" data-bs-toggle="tooltip" title="Has Approved"><i class="fas fa-check"></i></div>' : '' !!} {!! $collaborator->user->displayName !!}: {{ $collaborator->data }}
                                    </div>
                                @endif
                            @endforeach
                            <div class="mt-2 text-end">
                                {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                            </div>
                            {!! Form::close() !!}
                        @else
                            @foreach ($submission->collaborators as $collaborator)
                                <div class="d-flex">
                                    {!! $submission->status == 'Pending' && $collaborator->has_approved ? '<div class="mb-2 me-2 text-success" data-bs-toggle="tooltip" title="Has Approved"><i class="fas fa-check"></i></div>' : '' !!} {!! $collaborator->user->displayName !!}: {{ $collaborator->data }}
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
            @if ($submission->participants->count())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Participants</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($submission->participants as $participant)
                            <div class="d-flex">
                                {!! $participant->user->displayName !!}: {{ $participant->displayType }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($submission->characters->count())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Characters</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($submission->characters->chunk(1) as $chunk)
                            <div class="row mb-2">
                                @foreach ($chunk as $character)
                                    <div class="col-md">
                                        @include('galleries._character', ['character' => $character->character])
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($submission->promptSubmissions->count())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Prompt Submissions</h5>
                    </div>
                    <div class="card-body">
                        @foreach ($submission->promptSubmissions as $promptSubmission)
                            <strong><a href="{{ $promptSubmission->viewUrl }}">#{{ $promptSubmission->id }} for {!! $promptSubmission->prompt->name !!}</a></strong> by {!! $promptSubmission->user->displayName !!}<br />
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Mention This</h5>
                </div>
                <div class="card-body">
                    In the rich text editor:
                    <div class="alert alert-secondary">
                        [thumb={{ $submission->id }}]
                    </div>
                    @if (!config('lorekeeper.settings.wysiwyg_comments'))
                        In a comment:
                        <div class="alert alert-secondary">
                            @if (isset($submission->hash) && !isset($submission->content_warning))
                                [![Image]({{ $submission->thumbnailUrl }})]({{ $submission->url }})
                            @else
                                [{{ $submission->displayTitle }} by {{ $submission->creditsPlain }}
                                {{ isset($submission->hash) ? '(Art)' : '(Literature)' }}{{ isset($submission->content_warning) ? ' ・ **Content Warning:** ' . nl2br(htmlentities($submission->content_warning)) : '' }}]({{ $submission->url }})
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Comments -->
    @if ($submission->isVisible)
        <div class="container">
            @comments(['model' => $submission, 'perPage' => 5])
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.view-favorites').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('gallery/view/favorites') }}/{{ $submission->id }}", 'Favorited By');
            });
        });
    </script>
@endsection
