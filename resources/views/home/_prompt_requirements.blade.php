@if (count(getLimits($prompt)))
    <div class="alert alert-warning">
        <strong>Warning:</strong> If you are submitting a prompt, you will not be able to edit the contents after
        the submission has been made.
        <br />
        Submitting to: {!! $prompt->displayName !!}
    </div>
    @include('widgets._limits', [
        'object' => $prompt,
        'hideUnlock' => true,
    ])
    <div class="form-group float-right">
        {!! Form::label('confirm', 'I understand that I will not be able to edit this submission after it has been made.', ['class' => 'alert alert-info']) !!}
        {!! Form::checkbox('confirm', '1', false, ['class' => 'form-check-input', 'id' => 'confirm', 'required', 'data-on' => 'Yes', 'data-off' => 'No']) !!}
    </div>

    <script>
        $('.form-check-input').attr('data-toggle', 'toggle').bootstrapToggle();
        $('[data-toggle="tooltip"]').tooltip();
    </script>
@endif
