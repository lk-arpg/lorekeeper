@extends('home.layout')

@section('home-title')
    @if ($isClaim)
        Claim Draft
    @else
        Submission Draft
    @endif
@endsection

@section('home-content')
    @if ($isClaim)
        {!! breadcrumbs(['Claims' => 'claims', 'Claim Draft' => 'claims/drafts']) !!}
    @else
        {!! breadcrumbs(['Prompt Submissions' => 'submissions', 'Submission Draft' => 'submissions/drafts']) !!}
    @endif

    <h1>
        @if ($isClaim)
            Claim Draft
        @else
            Submission Draft
        @endif
    </h1>

    @if ($closed)
        <div class="alert alert-danger">
            The {{ $isClaim ? 'claim' : 'submission' }} queue is currently closed. You cannot edit {{ $isClaim ? 'claim' : 'submission' }} drafts at this time.
        </div>
    @else
        @include('home._submission_form', ['submission' => $submission, 'userGallerySubmissions' => $userGallerySubmissions])

        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">

                <div class="modal-content hide" id="confirmContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm {{ $isClaim ? 'Claim' : 'Submission' }}</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>
                            This will submit the form and put it into the {{ $isClaim ? 'claims' : 'prompt' }} approval queue.
                            You will not be able to edit the contents after the {{ $isClaim ? 'claim' : 'submission' }} has been made.
                            If you aren't certain that you are ready, consider saving as a draft instead.
                            Click the Confirm button to complete the {{ $isClaim ? 'claim' : 'submission' }}.
                        </p>
                        @if (!$isClaim)
                            <div id="requirementsWarning">
                                @if (count(getLimits($submission->prompt)))
                                    @include('home._prompt_requirements', ['prompt' => $submission->prompt])
                                @endif
                            </div>
                        @endif
                        <div class="text-right">
                            <a href="#" id="confirmSubmit" class="btn btn-primary">Confirm</a>
                        </div>
                    </div>
                </div>

                <div class="modal-content hide" id="draftContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Save Draft</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>
                            This will edit the existing {{ $submission->prompt_id ? 'submission' : 'claim' }} draft.
                            Items and other attachments will be held, similar to in design update drafts.
                        </p>
                        <div class="text-right">
                            <a href="#" id="draftSubmit" class="btn btn-success">Save Draft</a>
                        </div>
                    </div>
                </div>

                <div class="modal-content hide" id="cancelContent">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Delete Draft</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>
                            This will cancel the {{ $submission->prompt_id ? 'submission' : 'claim' }} draft and return any attachments to your inventories.
                        </p>
                        <div class="text-right">
                            <a href="#" id="cancelSubmit" class="btn btn-danger">Delete Draft</a>
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
        @if ($isClaim)
            @include('js._loot_js', ['showLootTables' => false, 'showRaffles' => true])
        @else
            @include('js._loot_js', ['showLootTables' => false, 'showRaffles' => false])
        @endif
        @include('js._character_select_js')
        @include('widgets._inventory_select_js')
        @include('widgets._bank_select_row', ['owners' => [Auth::user()]])
        @include('widgets._bank_select_js', [])

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

                var $cancelButton = $('#cancelButton');
                var $cancelContent = $('#cancelContent');
                var $cancelSubmit = $('#cancelSubmit');


                @if (!$isClaim)
                    var $prompt = $('#prompt');
                    var $rewards = $('#rewards');
                    var $requirementsWarning = $('#requirementsWarning');

                    $prompt.selectize();
                    $prompt.on('change', function(e) {
                        $rewards.load('{{ url('submissions/new/prompt') }}/' + $(this).val());
                        $requirementsWarning.html('');
                        $requirementsWarning.load('{{ url('submissions/new/prompt') }}/' + $(this).val() + '/requirements');
                    });
                @endif

                $confirmButton.on('click', function(e) {
                    e.preventDefault();
                    $confirmContent.removeClass('hide');
                    $draftContent.addClass('hide');
                    $cancelContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $confirmSubmit.on('click', function(e) {
                    e.preventDefault();
                    let $confirm = $('#requirementsWarning').find('#confirm').length ? $('#requirementsWarning').find('#confirm').is(':checked') : true;
                    if ("{{ !$isClaim }}" && !$confirm) {
                        alert('You must confirm that you understand that you will not be able to edit this submission after it has been made.');
                        return;
                    }
                    $submissionForm.attr('action', '{{ url()->current() }}/submit');
                    $submissionForm.submit();
                });

                $draftButton.on('click', function(e) {
                    e.preventDefault();
                    $draftContent.removeClass('hide');
                    $confirmContent.addClass('hide');
                    $cancelContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $draftSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}');
                    $submissionForm.submit();
                });

                $cancelButton.on('click', function(e) {
                    e.preventDefault();
                    $cancelContent.removeClass('hide');
                    $confirmContent.addClass('hide');
                    $draftContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $cancelSubmit.on('click', function(e) {
                    e.preventDefault();
                    $submissionForm.attr('action', '{{ url()->current() }}/delete');
                    $submissionForm.submit();
                });


            });
        </script>
    @endif
@endsection
