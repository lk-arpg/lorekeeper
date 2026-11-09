@extends('home.layout')

@section('home-title')
    New Submission
@endsection

@section('home-content')
    {!! breadcrumbs(['Reports' => 'reports', 'New Report' => 'reports/new']) !!}

    <h1>
        New Report
    </h1>

    @if ($closed)
        <div class="alert alert-danger">
            The report queue is currently closed. You cannot make a new report at this time.
        </div>
    @else
        {!! Form::open(['url' => 'reports/new', 'id' => 'submissionForm']) !!}
        <div class="br-form-group alert alert-warning" style="display: none">
            <div class="form-check">
                When submitting a bug report, please use the 'URL / Title' section to briefly summarise the bug. Include any links in the 'Comments' section. This is to allow an easy search.
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('url', 'URL / Title') !!}
            {!! add_help('Enter a URL relevant to your claim (for example, a comment proving you may make this claim). This field cannot be left blank.') !!}
            {!! Form::text('url', Request::get('url'), ['class' => 'form-control', 'required']) !!}
        </div>
        <div class="form-group">
            {!! Form::checkbox('is_br', 1, 0, ['class' => 'is-br-class form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_br', 'Is this report a bug report?', ['class' => 'is-br-label form-check-label']) !!} {!! add_help('Only check this box if it has not already been reported/you cannot find a matching bug in the bug report index.') !!}
        </div>
        <div class="br-form-group mb-2" style="display: none">
            {!! Form::label('error', 'Error type', ['class' => 'form-check-label mb-2']) !!} {!! add_help('What error best describes the bug?') !!}
            {!! Form::select('error', ['500' => '500 error', '404' => '404 error', 'text' => 'Text error', 'exploit' => 'Exploit', 'other' => 'Other error'], null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Type']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('comments', 'Comments (Optional)') !!} {!! add_help('Enter a comment for your report (no HTML). This will be viewed by the mods when reviewing your report.') !!}
            {!! Form::textarea('comments', null, ['class' => 'form-control']) !!}
        </div>

        <div class="text-right">
            <a href="#" class="btn btn-primary" id="submitButton">Submit</a>
        </div>
        {!! Form::close() !!}


        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0">Confirm Report</span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>This will submit the form and put it into the report review queue. You will not be able to edit the contents after the report has been made. Click the Confirm button to complete the report.</p>
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
    <script>
        $(document).ready(function() {
            var $submitButton = $('#submitButton');
            var $confirmationModal = $('#confirmationModal');
            var $formSubmit = $('#formSubmit');
            var $submissionForm = $('#submissionForm');

            $submitButton.on('click', function(e) {
                e.preventDefault();
                $confirmationModal.modal('show');
            });

            $formSubmit.on('click', function(e) {
                e.preventDefault();
                $submissionForm.submit();
            });
            $('.is-br-class').change(function(e) {
                console.log(this.checked)
                $('.br-form-group').css('display', this.checked ? 'block' : 'none')
            })
            $('.br-form-group').css('display', $('.is-br-class').prop('checked') ? 'block' : 'none')
        });
    </script>
@endsection
