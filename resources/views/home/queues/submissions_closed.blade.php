@extends('home.layout')

@section('home-title')
    New Submission
@endsection

@section('home-content')
    {!! breadcrumbs(['Queue Submissions' => 'queue-submissions', 'New Submission' => 'queue-submissions/new']) !!}

    <h1>

        {!! breadcrumbs(['Queue Submissions' => 'queue-submissions', 'New Submission' => 'queue-submissions/new']) !!}
    </h1>

    {!! Form::open(['url' => 'queue-submissions/new', 'id' => 'submissionForm']) !!}

    <div class="form-group">
        {!! Form::label('queue_id', 'Queue') !!}
        {!! Form::select('queue_id', $queues, null, ['class' => 'form-control selectize', 'id' => 'queue', 'placeholder' => '']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('Enter a comment for your submission (no HTML). This will be viewed by the mods when reviewing your submission.') !!}
        {!! Form::textarea('comments', null, ['class' => 'form-control']) !!}
    </div>

    @if ($queue->configSet('character_submit'))
        <h2>Characters</h2>

        <div id="characters" class="mb-3">
        </div>
        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="addCharacter">Add Character</a>
        </div>
    @endif

    <div class="text-right">
        <a href="#" class="btn btn-primary" id="submitButton">Submit</a>
    </div>
    {!! Form::close() !!}

    @if ($queue->configSet('character_submit') && View::exists('home.queues.types.characters.' . $queue->queue_type . '_select'))
        @include('home.queues.types.characters.' . $queue->queue_type . '_select')
    @endif

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Submission</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will submit the form and put it into the approval queue. You will not be able to edit the contents after the submission has been made. Click the Confirm
                        button to complete the submission.</p>
                    <div class="text-right">
                        <a href="#" id="formSubmit" class="btn btn-primary">Confirm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
