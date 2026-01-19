@extends('home.layout')

@section('home-title')
    Submission #{{ $submission->id }}
@endsection

@section('home-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Submission (#' . $submission->id . ')' => $submission->viewUrl]) !!}

    @include('home.queues._submission_content', ['submission' => $submission])

    @auth
        @if ($submission->user_id == Auth::user()->id && $submission->status == 'Pending')
            {!! Form::open(['url' => url()->current(), 'id' => 'submissionForm']) !!}

            <div class="text-right">
                <a href="#" class="btn btn-danger mr-2" id="cancellationButton">Cancel submission</a>
            </div>
            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content hide" id="cancellationContent">
                        <div class="modal-header">
                            <span class="modal-title h5 mb-0">Confirm Cancellation</span>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Make a mistake?
                                This will cancel this submission and return it to your drafts.
                            </p>
                            <div class="text-right">
                                <a href="#" id="cancellationSubmit" class="btn btn-danger">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {!! Form::close() !!}
        @endif
    @endauth
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            var $confirmationModal = $('#confirmationModal');
            var $submissionForm = $('#submissionForm');

            var $cancellationButton = $('#cancellationButton');
            var $cancellationContent = $('#cancellationContent');
            var $cancellationSubmit = $('#cancellationSubmit');

            $cancellationButton.on('click', function(e) {
                e.preventDefault();
                $cancellationContent.removeClass('hide');
                $confirmationModal.modal('show');
            });

            $cancellationSubmit.on('click', function(e) {
                e.preventDefault();
                $submissionForm.attr('action', '{{ url('/queue-submissions/draft/' . $submission->id) }}/cancel');
                $submissionForm.submit();
            });
        });
    </script>
@endsection
