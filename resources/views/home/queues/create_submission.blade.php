@extends('home.layout')

@section('home-title')
    New Submission
@endsection

@section('home-content')
    {!! breadcrumbs(['Queue Submissions' => 'queue-submissions', 'New Submission' => 'queue-submissions/new/' . $queue->id]) !!}

    <h1>
        New Submission
    </h1>

    @if ($closed)
        <div class="alert alert-danger">
            The queue is currently closed. You cannot make a new submission at this time.
        </div>
    @else
        @include('home.queues._submission_form', ['submission' => $submission])
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">

                <div class="modal-content hide" id="confirmContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Submission</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>
                            This will submit the form and put it into the approval queue.
                            You will not be able to edit the contents after the submission has been made.
                            If you aren't certain that you are ready, consider saving as a draft instead.
                            Click the Confirm button to complete the submission.
                        </p>
                        <div class="text-right">
                            <a href="#" id="confirmSubmit" class="btn btn-primary">Confirm</a>
                        </div>
                    </div>
                </div>

                <div class="modal-content hide" id="draftContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Create Draft</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>
                            This will place the submission into your drafts.
                            Items and other attachments will be held, similar to in design update drafts.
                        </p>
                        <div class="text-right">
                            <a href="#" id="draftSubmit" class="btn btn-success">Save as Draft</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    @if (!$closed)
        @if ($queue->configSet('character_submit') && View::exists('home.queues.types.characters.' . $queue->queue_type . '_select_js'))
            @include('home.queues.types.characters.' . $queue->queue_type . '_select_js')
        @endif
        @if ($queue->configSet('item_consume'))
            @include('widgets._inventory_select_js')
            @include('widgets._bank_select_row', ['owners' => [Auth::user()]])
            @include('widgets._bank_select_js', [])
        @endif

        <script>
            $(document).ready(function() {
                var $confirmationModal = $('#confirmationModal');
                var $submissionForm = $('#submissionForm');

                var $confirmButton = $('#confirmButton');
                var $confirmContent = $('#confirmContent');
                var $confirmSubmit = $('#confirmSubmit');

                var $draftButton = $('#draftButton');
                var $draftContent = $('#draftContent');
                var $draftSubmit = $('#draftSubmit');

                $confirmButton.on('click', function(e) {
                    e.preventDefault();
                    $confirmContent.removeClass('hide');
                    $draftContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $confirmSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}');
                    $submissionForm.submit();
                });

                $draftButton.on('click', function(e) {
                    e.preventDefault();
                    $draftContent.removeClass('hide');
                    $confirmContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $draftSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/draft');
                    $submissionForm.submit();
                });
            });
        </script>
    @endif
@endsection
