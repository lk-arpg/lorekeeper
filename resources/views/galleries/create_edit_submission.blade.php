@extends('galleries.layout')

@section('gallery-title')
    {{ $submission->id ? 'Edit' : 'Create' }} Submission
@endsection

@section('gallery-content')
    {!! breadcrumbs([
        'Gallery' => 'gallery',
        $gallery->name => 'gallery/' . $gallery->id,
        ($submission->id ? 'Edit' : 'Create') . ' Submission' => $submission->id ? 'gallery/submissions/edit/' . $submission->id : 'gallery/submit/' . $gallery->id,
    ]) !!}

    <h1>
        {{ $submission->id ? 'Edit Submission (#' . $submission->id . ', "' . $submission->displayTitle . '")' : 'Submit to ' . $gallery->name }}
        @if ($submission->id)
            <div class="float-right">
                @if ($submission->status == 'Accepted')
                    <a href="#" class="btn btn-warning archive-submission-button">{{ $submission->isVisible ? 'Archive' : 'Unarchive' }}</a>
                @endif
                <a href="/gallery/view/{{ $submission->id }}" class="btn btn-outline-primary">View Submission</a>
            </div>
        @endif
    </h1>

    @if (!$submission->id && ($closed || !$gallery->canSubmit(Settings::get('gallery_submissions_open'), Auth::user())))
        <div class="alert alert-danger">
            @if ($closed)
                Gallery submissions are currently closed.
            @else
                You can't submit to this gallery.
            @endif
        </div>
    @else
        {!! Form::open(['url' => $submission->id ? 'gallery/edit/' . $submission->id : 'gallery/submit', 'id' => 'gallerySubmissionForm', 'files' => true]) !!}

        <h2>Main Content</h2>
        <p>Upload an image and/or text as the content of your submission. You <strong>can</strong> upload both in the event that you have an image with accompanying text or vice versa.</p>

        <div class="form-group" id="imageForm">
            {!! Form::label('image', 'Image Upload', ['class' => 'h5']) !!}
            @if ($submission->id && isset($submission->hash) && $submission->hash)
                <div class="card mb-2" id="existingImage">
                    <div class="card-body text-center">
                        <img src="{{ $submission->imageUrl }}" style="max-width:100%; max-height:60vh;" alt="Image submission" />
                    </div>
                </div>
            @endif
            <div class="card mb-2 hide" id="imageContainer">
                <div class="card-body text-center">
                    <img src="#" id="image" style="max-width:100%; max-height:60vh;" alt="Image submission" />
                </div>
            </div>
            <div class="card p-2">
                <div class="custom-file">
                    {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                    {!! Form::file('image', ['class' => 'custom-file-input', 'id' => 'mainImage']) !!}
                </div>
                <small>Images may be PNG, GIF, JPG, or WebP and up to 3MB in size.</small>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('text', 'Writing / Text', ['class' => 'h5']) !!} {!! add_help('If you have a text submission, you can paste it here. You can also use the WYSIWYG editor to format your text. If you have an image submission, you can leave this blank or add a text to supplement your image submission.') !!}
            @if (config('lorekeeper.settings.hide_textarea_on_gallery_submissions.enable'))
                <a href="#writingForm" id="writingFormCollapseBtn" class="mx-2 mb-2 btn btn-sm btn-primary" data-toggle="collapse" aria-expanded="false">Hide Textarea</a>
            @endif
            <div id="writingForm" class="collapse show">
                {!! Form::textarea('text', $submission->text ?? old('text'), ['class' => 'form-control wysiwyg']) !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md">
                <h3>Basic Information</h3>
                <div class="form-group">
                    {!! Form::label('title', 'Title', ['class' => 'h5']) !!} {!! add_help('You <strong>do not</strong> need to indicate that a piece is a trade, gift, for a prompt etc. as this will be automatically added based on your input elsewhere in this form.') !!}
                    {!! Form::text('title', $submission->title ?? old('title'), ['class' => 'form-control']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('description', 'Description (Optional)', ['class' => 'h5']) !!}
                    {!! Form::textarea('description', $submission->description ?? old('description'), ['class' => 'form-control wysiwyg']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('content_warning', 'Content Warning (Optional)', ['class' => 'h5']) !!} {!! add_help(
                        'Provide a succinct content warning for the piece if necessary. If a content warning is provided, the thumbnail will be replaced with a generic image and the warning displayed under it. The piece will be displayed in full on its page, however.',
                    ) !!}
                    {!! Form::text('content_warning', $submission->content_warning ?? old('content_warning'), ['class' => 'form-control']) !!}
                </div>

                @if ($gallery->prompt_selection == 1 && (!$submission->id || Auth::user()->hasPower('manage_submissions')))
                    <div class="form-group">
                        {!! Form::label('prompt_id', ($submission->id && Auth::user()->hasPower('manage_submissions') ? '[Admin] ' : '') . 'Prompt (Optional)') !!} {!! add_help(
                            'This <strong>does not</strong> automatically submit to the selected prompt, and you will need to submit to it separately. The prompt selected here will be displayed on the submission page for future reference. You will not be able to edit this after creating the submission.',
                        ) !!}
                        {!! Form::select('prompt_id', $prompts, $submission->prompt_id ?? old('prompt_id'), ['class' => 'form-control selectize', 'id' => 'prompt', 'placeholder' => 'Select a Prompt']) !!}
                    </div>
                @else
                    {!! $submission->prompt_id ? '<p><strong>Prompt:</strong> ' . $submission->prompt->displayName . '</p>' : '' !!}
                @endif

                @if ($submission->id && Auth::user()->hasPower('manage_submissions'))
                    <div class="form-group">
                        {!! Form::label('gallery_id', '[Admin] Gallery / Move Submission') !!} {!! add_help(
                            'Use in the event you need to move a submission between galleries. If left blank, leaves the submission in its current location. Note that if currency rewards from submissions are enabled, this won\'t retroactively fill out the form if moved from a gallery where they are disabled to one where they are enabled.',
                        ) !!}
                        {!! Form::select('gallery_id', $galleryOptions, null, ['class' => 'form-control selectize gallery-select original', 'id' => 'gallery', 'placeholder' => '']) !!}
                    </div>
                @endif

                @if (!$submission->id)
                    {!! Form::hidden('gallery_id', $gallery->id) !!}
                @endif

                <h3>Characters</h3>
                <p>
                    Add the characters included in this piece.
                    @if (Settings::get('gallery_submissions_reward_currency'))
                        This helps the staff processing your submission award {!! $currency->displayName !!} for it, so be sure to add every character.
                    @endif
                </p>
                <div id="characters" class="mb-3">
                    @if ($submission->id)
                        @foreach ($submission->characters as $character)
                            @include('galleries._character_select_entry', ['character' => $character])
                        @endforeach
                    @endif
                    @if (old('slug'))
                        @foreach (array_unique(old('slug')) as $slug)
                            @include('galleries._character_select_entry', ['character' => \App\Models\Character\Character::where('slug', $slug)->first()])
                        @endforeach
                    @endif
                </div>
                <div class="text-right mb-3">
                    <a href="#" class="btn btn-outline-info" id="addCharacter">Add Character</a>
                </div>
            </div>
            @if (!$submission->id || $submission->status == 'Pending')
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Collaborators</h5>
                        </div>
                        <div class="card-body">
                            <p>If this piece is a collaboration, add collaborators and their roles here, including yourself. <strong>Otherwise, leave this blank</strong>. You <strong>will not</strong> be able to edit this once the submission has been
                                accepted, but will while it is still pending.</p>
                            @if (!$submission->id || $submission->status == 'Pending')
                                <div class="text-right mb-3">
                                    <a href="#" class="btn btn-outline-info" id="add-collaborator">Add Collaborator</a>
                                </div>
                                <div id="collaboratorList">
                                    @if ($submission->id)
                                        @foreach ($submission->collaborators as $collaborator)
                                            <div class="mb-2">
                                                <div class="d-flex">{!! $collaborator->has_approved ? '<div class="btn btn-success mb-2 mr-2" data-toggle="tooltip" title="Has Approved"><i class="fas fa-check"></i></div>' : '' !!}{!! Form::select('collaborator_id[]', $users, $collaborator->user_id, ['class' => 'form-control mr-2 collaborator-select original', 'placeholder' => 'Select User']) !!}</div>
                                                <div class="d-flex">
                                                    {!! Form::text('collaborator_data[]', $collaborator->data, ['class' => 'form-control mr-2', 'placeholder' => 'Role (Sketch, Lines, etc.)']) !!}
                                                    <a href="#" class="remove-collaborator btn btn-danger mb-2">×</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if (old('collaborator_id'))
                                        @foreach (old('collaborator_id') as $key => $collaborator)
                                            <div class="mb-2">
                                                <div class="d-flex">{!! Form::select('collaborator_id[]', $users, $collaborator, ['class' => 'form-control mr-2 collaborator-select original', 'placeholder' => 'Select User']) !!}</div>
                                                <div class="d-flex">
                                                    {!! Form::text('collaborator_data[]', old('collaborator_data')[$key], ['class' => 'form-control mr-2', 'placeholder' => 'Role (Sketch, Lines, etc.)']) !!}
                                                    <a href="#" class="remove-collaborator btn btn-danger mb-2">×</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @else
                                <p>
                                    @if ($submission->collaborators->count())
                                        @foreach ($submission->collaborators as $collaborator)
                                            {!! $collaborator->user->displayName !!}: {{ $collaborator->data }}<br />
                                        @endforeach
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Other Participants</h5>
                        </div>
                        <div class="card-body">
                            <p>If this piece is gift, part of a trade, or was commissioned, specify the related user(s) here and select their role. <strong>Otherwise, leave this blank</strong>. You <strong>will not</strong> be able to edit this once the
                                submission has been accepted, but will while it is still pending.</p>
                            @if (!$submission->id || $submission->status == 'Pending')
                                <div class="text-right mb-3">
                                    <a href="#" class="btn btn-outline-info" id="add-participant">Add Participant</a>
                                </div>
                                <div id="participantList">
                                    @if ($submission->id)
                                        @foreach ($submission->participants as $participant)
                                            <div class="mb-2">
                                                <div class="d-flex">{!! Form::select('participant_id[]', $users, $participant->user_id, ['class' => 'form-control mr-2 participant-select original', 'placeholder' => 'Select User']) !!}</div>
                                                <div class="d-flex">
                                                    {!! Form::select('participant_type[]', ['Gift' => 'Gift For', 'Trade' => 'Traded For', 'Comm' => 'Commissioned', 'Comm (Currency)' => 'Commissioned (' . $currency->name . ')'], $participant->type, [
                                                        'class' => 'form-control mr-2',
                                                        'placeholder' => 'Select Role',
                                                    ]) !!}
                                                    <a href="#" class="remove-participant btn btn-danger mb-2">×</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if (old('participant_id'))
                                        @foreach (old('participant_id') as $key => $participant)
                                            <div class="mb-2">
                                                <div class="d-flex">{!! Form::select('participant_id[]', $users, $participant, ['class' => 'form-control mr-2 participant-select original', 'placeholder' => 'Select User']) !!}</div>
                                                <div class="d-flex">
                                                    {!! Form::select('participant_type[]', ['Gift' => 'Gift For', 'Trade' => 'Traded For', 'Comm' => 'Commissioned', 'Comm (Currency)' => 'Commissioned (' . $currency->name . ')'], old('participant_type')[$key], [
                                                        'class' => 'form-control mr-2',
                                                        'placeholder' => 'Select Role',
                                                    ]) !!}
                                                    <a href="#" class="remove-participant btn btn-danger mb-2">×</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @else
                                <p>
                                    @if ($submission->participants->count())
                                        @foreach ($submission->participants as $participant)
                                            {!! $participant->user->displayName !!}: {{ $participant->displayType }}<br />
                                        @endforeach
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                    @if (Settings::get('gallery_submissions_reward_currency') && $gallery->currency_enabled && !$submission->id)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>{!! $currency->name !!} Awards</h5>
                            </div>
                            <div class="card-body">
                                <p>Please select options as appropriate for this piece. This will help the staff processing your submission award {!! $currency->displayName !!} for it. You <strong>will not</strong> be able to edit this after creating the
                                    submission.</p>

                                @foreach (config('lorekeeper.group_currency_form') as $key => $field)
                                    <div class="form-group">
                                        @if ($field['type'] == 'checkbox')
                                            <input class="form-check-input ml-0 pr-4" name="{{ $key }}" type="checkbox" value="{{ isset($field['value']) ? $field['value'] : 1 }}">
                                        @endif
                                        @if (isset($field['label']))
                                            {!! Form::label(isset($field['multiple']) && $field['multiple'] ? $key . '[]' : $key, $field['label'], [
                                                'class' => 'label-class' . ($field['type'] == 'checkbox' ? ' ml-3' : '') . (isset($field['rules']) && $field['rules'] ? ' ' . $field['rules'] : ''),
                                            ]) !!}
                                        @endif
                                        @if ($field['type'] == 'choice' && isset($field['choices']))
                                            @foreach ($field['choices'] as $value => $choice)
                                                <div class="choice-wrapper">
                                                    <input class="form-check-input ml-0 pr-4" name="{{ isset($field['multiple']) && $field['multiple'] ? $key . '[]' : $key }}"
                                                        id="{{ isset($field['multiple']) && $field['multiple'] ? $key . '[]' : $key . '_' . $value }}" type="{{ isset($field['multiple']) && $field['multiple'] ? 'checkbox' : 'radio' }}"
                                                        value="{{ $value }}">
                                                    <label for="{{ $key }}[]" class="label-class ml-3">{{ $choice }}</label>
                                                </div>
                                            @endforeach
                                        @elseif($field['type'] != 'checkbox')
                                            <input class="form-control" name="{{ $key }}" type="{{ $field['type'] }}" id="{{ $key }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if ($submission->id && Auth::user()->id != $submission->user->id && Auth::user()->hasPower('manage_submissions'))
            <div class="form-group">
                {!! Form::checkbox('alert_user', 1, true, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-onstyle' => 'danger']) !!}
                {!! Form::label('alert_user', 'Notify User', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will send a notification to the user that either their submission has been edited or moved. It does not send both notifications, preferring the move notification if relevant.') !!}
            </div>
        @endif

        <div class="text-right">
            <a href="#" class="btn btn-primary" id="submitButton">Submit</a>
        </div>
        {!! Form::close() !!}

        @include('galleries._character_select')
        <div class="collaborator-row hide mb-2">
            {!! Form::select('collaborator_id[]', $users, null, ['class' => 'form-control mr-2 collaborator-select', 'placeholder' => 'Select User']) !!}
            <div class="d-flex">
                {!! Form::text('collaborator_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Role (Sketch, Lines, etc.)']) !!}
                <a href="#" class="remove-collaborator btn btn-danger mb-2">×</a>
            </div>
        </div>
        <div class="participant-row hide mb-2">
            {!! Form::select('participant_id[]', $users, null, ['class' => 'form-control mr-2 participant-select', 'placeholder' => 'Select User']) !!}
            <div class="d-flex">
                {!! Form::select('participant_type[]', ['Gift' => 'Gift For', 'Trade' => 'Traded For', 'Comm' => 'Commissioned', 'Comm (Currency)' => 'Commissioned (' . $currency->name . ')'], null, [
                    'class' => 'form-control mr-2',
                    'placeholder' => 'Select Role',
                ]) !!}
                <a href="#" class="remove-participant btn btn-danger mb-2">×</a>
            </div>
        </div>

        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Submission</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>
                            @if (!$submission->id)
                                This will submit the form and put it into the approval queue. You will not be able to edit certain parts after the submission has been made, so please review the contents before submitting. Click the Confirm button to
                                complete the submission.
                            @else
                                This will update the
                                submission.{{ $submission->status == 'Pending' ? ' If you have added or removed any collaborators, they will not be informed (so as not to send additional notifications to previously notified collaborators), so please make sure to do so if necessary as all collaborators must approve a submission for it to be submitted for admin approval.' : '' }}
                            @endif
                        </p>
                        <div class="text-right">
                            <a href="#" id="formSubmit" class="btn btn-primary">Confirm</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    @if (!$closed || ($submission->id && $submission->status != 'Rejected'))
        @include('galleries._character_select_js')
        @include('js._tinymce_wysiwyg')
        <script>
            $(document).ready(function() {
                var $submitButton = $('#submitButton');
                var $confirmationModal = $('#confirmationModal');
                var $formSubmit = $('#formSubmit');
                var $gallerySubmissionForm = $('#gallerySubmissionForm');

                $submitButton.on('click', function(e) {
                    e.preventDefault();
                    $confirmationModal.modal('show');
                });

                $formSubmit.on('click', function(e) {
                    e.preventDefault();
                    $gallerySubmissionForm.submit();
                });

                $('.archive-submission-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('gallery/archive') }}/{{ $submission->id }}", 'Archive Submission');
                });

                $('.original.collaborator-select').selectize();
                $('#add-collaborator').on('click', function(e) {
                    e.preventDefault();
                    addCollaboratorRow();
                });
                $('.remove-collaborator').on('click', function(e) {
                    e.preventDefault();
                    removeCollaboratorRow($(this));
                })

                function addCollaboratorRow() {
                    var $clone = $('.collaborator-row').clone();
                    $('#collaboratorList').append($clone);
                    $clone.removeClass('hide collaborator-row');
                    $clone.find('.remove-collaborator').on('click', function(e) {
                        e.preventDefault();
                        removeCollaboratorRow($(this));
                    })
                    $clone.find('.collaborator-select').selectize();
                }

                function removeCollaboratorRow($trigger) {
                    $trigger.parent().parent().remove();
                }

                $('.original.participant-select').selectize();
                $('#add-participant').on('click', function(e) {
                    e.preventDefault();
                    addParticipantRow();
                });
                $('.remove-participant').on('click', function(e) {
                    e.preventDefault();
                    removeParticipantRow($(this));
                })

                function addParticipantRow() {
                    var $clone = $('.participant-row').clone();
                    $('#participantList').append($clone);
                    $clone.removeClass('hide participant-row');
                    $clone.find('.remove-participant').on('click', function(e) {
                        e.preventDefault();
                        removeParticipantRow($(this));
                    })
                    $clone.find('.participant-select').selectize();
                }

                function removeParticipantRow($trigger) {
                    $trigger.parent().parent().remove();
                }

                var $image = $('#image');

                function readURL(input) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            $image.attr('src', e.target.result);
                            $('#existingImage').addClass('hide');
                            $('#imageContainer').removeClass('hide');
                        }
                        reader.readAsDataURL(input.files[0]);
                        @if (config('lorekeeper.settings.hide_textarea_on_gallery_submissions.enable') && config('lorekeeper.settings.hide_textarea_on_gallery_submissions.on_image'))
                            // hide text editor if image is uploaded
                            $('#writingForm').collapse('hide')
                        @endif
                    } else {
                        @if (config('lorekeeper.settings.hide_textarea_on_gallery_submissions.enable') && config('lorekeeper.settings.hide_textarea_on_gallery_submissions.on_image'))
                            $('#writingForm').collapse('show')
                        @endif
                    }
                }
                $("#mainImage").change(function() {
                    readURL(this);
                });
                @if (config('lorekeeper.settings.hide_textarea_on_gallery_submissions.enable'))
                    $('#writingForm').on('hide.bs.collapse', function() {
                        $('#writingFormCollapseBtn').text("Show Textarea");
                    })
                    $('#writingForm').on('show.bs.collapse', function() {
                        $('#writingFormCollapseBtn').text("Hide Textarea");
                    })
                @endif

                $('.original.gallery-select').selectize();
            });
        </script>
    @endif
@endsection
