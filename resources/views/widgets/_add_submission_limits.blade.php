@php
    $limit_periods = config('lorekeeper.extensions.limit_periods');
@endphp

<div class="card mb-3">
    <div class="card-body">
        <h3>Submission Limits</h3>
        <p>Limit the number of times a user can submit. Leave blank to allow endless submissions.</p>
        <p>Set a number into number of submissions. This will be applied for all time if you leave period blank, or per time period (ex: once a month, twice a week) if selected.</p>
        <p>If you turn 'per character' on, then the number of submissions multiplies per character (ex: if you can submit twice a month per character and you own three characters, that's 6 submissions) HOWEVER it will not keep track of which
            characters are being submitted due to conflicts arising in character cameos. A user will be able to submit those full 6 times with just one character...!</p>
        <div class="row">
            <div class="col-md-6 form-group">
                {!! Form::label('limit', 'Number of Submissions (Optional)') !!} {!! add_help('Enter a number to limit how many times a user can submit. Leave blank to allow endless submissions.') !!}
                {!! Form::text('limit', $object->limit, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md-6 form-group">
                {!! Form::label('limit_period', 'Limit Period') !!} {!! add_help('The time period that the limit is set for.') !!}
                {!! Form::select('limit_period', $limit_periods, $object->limit_period, ['class' => 'form-control', 'data-name' => 'limit_period']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::checkbox('limit_character', 1, $object->limit_character, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('limit_character', 'Per Character', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, they can submit N times per character they own on the masterlist.') !!}
        </div>
        @if (get_class($object) == 'App\Models\Queue\Queue')
            <div class="form-group">
                {!! Form::label('limit_concurrent', 'Concurrent Limit (Optional)') !!} {!! add_help(
                    'A limit to concurrent submissions (applies regardless of the limit above). This will check if the user has any pending or draft submissions and prevents them from making more after they hit the cap until they are processed. Leave blank to enforce no limit to concurrent submissions.',
                ) !!}
                {!! Form::text('limit_concurrent', $object->limit_concurrent, ['class' => 'form-control']) !!}
            </div>
        @endif
    </div>
</div>
