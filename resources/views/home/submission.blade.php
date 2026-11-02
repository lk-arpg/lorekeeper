@extends('home.layout')

@section('home-title')
    {{ $submission->prompt_id ? 'Submission' : 'Claim' }} (#{{ $submission->id }})
@endsection

@section('home-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, $submission->prompt_id ? 'Submission' : 'Claim (#' . $submission->id . ')' => $submission->viewUrl]) !!}

    @include('home._submission_content', ['submission' => $submission, 'isClaim' => $isClaim])

    @auth
        @if ($submission->user_id == Auth::user()->id && $submission->status == 'Pending')
            {!! Form::open(['url' => url()->current(), 'id' => 'submissionForm']) !!}
            @if ($isClaim || !count(getLimits($submission->prompt)))
                <div class="text-right">
                    <a href="#" class="btn btn-danger mr-2" id="cancellationButton">Cancel {{ $submission->prompt_id ? 'submission' : 'claim' }}</a>
                </div>
            @else
                <div class="alert alert-warning">
                    This prompt cannot be edited after submission. If you need to make changes, please contact a staff member.
                </div>
            @endif
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
                                This will cancel this {{ $submission->prompt_id ? 'submission' : 'claim' }} and return it to your drafts.
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
                $submissionForm.attr('action', '{{ url('/' . ($submission->prompt_id ? 'submissions' : 'claims') . '/draft/' . $submission->id) }}/cancel');
                $submissionForm.submit();
            });
        });
    </script>
@endsection
